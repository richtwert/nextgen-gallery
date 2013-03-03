<?php

/**
 * Provides a wp-admin page to manage NextGEN Settings
 */
class C_NextGen_Settings_Controller extends C_NextGen_Backend_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Settings_Controller');
		$this->implement('I_Settings_Manager_Controller');
	}

	/**
	 * Gets an instance of the controller
	 * @param string $context
	 * @return C_NextGen_Settings_Controller
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = function_exists('get_called_class') ?
				get_called_class() : get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provides controller actions and help methods
 */
class Mixin_NextGen_Settings_Controller extends Mixin
{
	function enqueue_backend_resources()
	{
		$this->call_parent('enqueue_backend_resources');

		wp_enqueue_script(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.js'),
			array('jquery-ui-accordion', 'wp-color-picker'),
			$this->module_version
		);

		wp_enqueue_style(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.css'),
			array(),
			$this->module_version
		);
	}


	/**
	 * Returns the contents of the "Options" page
	 */
	function index_action()
	{
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_token = $security->get_request_token('nextgen_edit_settings');
		$sec_actor = $security->get_current_actor();

		if (!$sec_actor->is_allowed('nextgen_edit_settings'))
		{
			echo __('No permission.', 'nggallery');

			return;
		}

		// Enqueue resources
		$this->object->enqueue_backend_resources();

		$settings = $this->object->get_registry()->get_utility('I_Settings_Manager');
		$message = null;

		// Is this post a request? If so, process the request
		if ($this->is_post_request()) {
			if (!$sec_token->check_current_request()) {
				$message = '<div class="entity_errors">' . __('The request has expired. Please refresh the page.', 'nggallery') . '</div>';
			}
			else {
				$view_params = $this->object->_process_post_request($settings);
			}
		}

		// Set other view params
		$view_params['page_heading'] = $this->object->_get_options_page_heading();
		$view_params['tabs']		 = $this->object->_get_tabs($settings);
		$view_params['form_header']  = $sec_token->get_form_html();

		if ($message != null)
		{
			$view_params['message'] = $message;
		}

		// Render view
		$this->render_partial('nextgen_settings_page', $view_params);
	}

    /**
     * Saves a new settings array, refreshes the available watermark, and reverts the settings changes
     */
    function watermark_update_action()
    {
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_token = $security->get_request_token('nextgen_edit_settings');
		$sec_actor = $security->get_current_actor();

		if (!$sec_actor->is_allowed('nextgen_edit_settings'))
		{
			echo __('No permission.', 'nggallery');

			return;
		}

        $settings = $this->object->get_registry()->get_utility('I_Settings_Manager');
        $original_settings = $settings->to_array();

        if (($params = $this->object->param('settings')))
        {
            // Try saving the settings
            foreach ($params as $k => $v) {
                $settings->$k = $v;
            }

            if ($settings->is_valid())
            {
                $settings->save();

                // generate new watermark preview
                $thumbnail_url = $this->object->refresh_watermark_preview();

                // and quickly revert back to the original settings..
                foreach ($original_settings as $k => $v) {
                    $settings->$k = $v;
                }
                $settings->save();
            }
        }

        print json_encode(array('thumbnail_url' => $thumbnail_url));
    }

    /**
     * Refreshes (or creates) a new watermark preview.
     *
     * @return string File URL
     */
    function refresh_watermark_preview()
    {
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_token = $security->get_request_token('nextgen_edit_settings');
		$sec_actor = $security->get_current_actor();

		if (!$sec_actor->is_allowed('nextgen_edit_settings'))
		{
			echo __('No permission.', 'nggallery');

			return;
		}

        $dynthumbs = $this->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
        $imap      = $this->object->get_registry()->get_utility('I_Image_Mapper');
        $storage   = $this->object->get_registry()->get_utility('I_Gallery_Storage');

        $image = $imap->find_first();
        $parameters = array(
            'quality'   => 100,
            'height'    => 250,
            'crop'      => FALSE,
            'watermark' => TRUE
        );
        $size = $dynthumbs->get_size_name($parameters);
        $image = $storage->generate_image_size($image, $size, $parameters);
        return str_replace(ABSPATH, site_url() . '/', $image->fileName);
    }

	/**
	 * Processes the POST request
	 * @param C_NextGen_Settings $settings
	 */
	function _process_post_request($settings)
	{
		$retval	= array();
		$multi	= FALSE;

        // WARNING: this will reset all options in I_Settings_Manager to their defaults
        if (!empty($_POST['resetdefault']))
        {
            $new_settings = $this->object->get_registry()->get_utility('I_Settings_Manager');

            if ($new_settings->is_multisite())
            {
                $multi = $this->object->get_registry()
                                      ->get_utility('I_Settings_Manager', array('multisite'))
                                      ->reset(TRUE);
            }
            $single = $new_settings->reset(TRUE);

            if ($single || $multi)
                $retval['message'] = $this->object->show_success_for($settings, 'NextGEN Gallery Settings', TRUE);
            else
                $retval['message'] = $this->object->show_errors_for($settings, TRUE);

            return $retval;
        }

		// Do we have sufficient data to continue?
		if (($params = $this->object->param('settings')))
        {
            // if the provided gallery-path has problems we must bail before saving our other settings
            $result = $this->object->_save_gallery_path($settings, $params);
            if (!is_null($result))
                return $result;

			// Try saving the settings
			foreach ($params as $k => $v) {
                $settings->$k = $v;
            }

			// Save lightbox effects settings
			if ($settings->is_valid())
            {
				$this->object->_save_lightbox_library($settings);
				$this->object->_save_stylesheet_contents($settings->CSSfile);
                $this->object->_save_image_slugs($settings);
                $this->object->_flush_nextgen_cache();
			}

			// Save the changes made to the settings
			if ($settings->save())
            {
				$retval['message'] = $this->object->show_success_for($settings, 'NextGEN Gallery Settings', TRUE);
			}
			// Save failed. Display validation errors
			else {
				$retval['message'] = $this->object->show_errors_for($settings, TRUE);
			}
 		}
		// Insufficient data - illegal request
		else {
			$error_msg = _("Invalid request");
			$retval['message'] = "<div class='error entity_errors'>{$error_msg}</div>";
		}

		return $retval;
	}


	/**
	 * Returns the page heading for the "Options" page
	 */
	function _get_options_page_heading()
	{
		return _('NextGEN Gallery Options');
	}

	/**
	 * Returns a list of tabs to render for the "Options" page
	 * @return type
	 */
	function _get_tabs($settings)
	{
		$tabs = array(
			_('Image Options')			=> $this->object->_render_image_options_tab($settings),
			_('Thumbnail Options')			=> $this->object->_render_thumbnail_options_tab($settings),
			_('Lightbox Effect')		=> $this->object->_render_lightbox_library_tab($settings),
			_('Watermarks')				=> $this->object->_render_watermarks_tab($settings),
			_('Styles')					=> $this->object->_render_styling_tab($settings),
			_('Roles / Capabilities')	=> $this->object->_render_roles_tab($settings),
			_('Miscellaneous')			=> $this->object->_render_misc_tab($settings),
            _('Cache')                  => $this->object->_render_cache_tab($settings),
            _('Reset / Uninstall')      => $this->object->_render_reset_tab($settings)
		);

		if (is_multisite()) {
			$tabs['Multisite Options'] = $this->object->_render_multisite_options_tab($settings);
		}

		return $tabs;
	}

    /**
     * Renders a form controlling the deactivator cache
     *
     * @param C_NextGen_Settings $settings
     * @return string Rendered HTML
     */
    function _render_cache_tab($settings)
    {
        return $this->object->render_partial(
            'cache_tab',
            array(
                'flush_cache_label' => _('Clear cache'),
                'flush_cache_value' => _('Clear all NextGEN cache folders'),
                'flush_cache_tooltip' => _('Purge the disk of all cached image files')
            ),
            TRUE
        );
    }

	/**
	 * Renders a form to managing NextGEN Multisite Settings
	 */
	function _render_multisite_options_tab($settings)
	{
		echo 'Multisite Options go here';
	}


	/**
	 * Renders the roles tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_roles_tab($settings)
	{
		$view = path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
			'admin', 'roles.php'
		)));
		include_once ( $view );
		ob_start();
		nggallery_admin_roles();
		$retval = ob_get_contents();
		ob_end_clean();
		return $retval;
	}


	function _render_misc_tab($settings)
	{
		return $this->object->render_partial('misc_tab', array(
			'mediarss_activated'		=>		$settings->useMediaRSS,
			'mediarss_activated_label'	=>		_('Add MediaRSS link?'),
			'mediarss_activated_help'	=>		_('When enabled, adds a MediaRSS link to your header. Third-party web services can use this to publish your galleries'),
			'mediarss_activated_no'		=>		_('No'),
			'mediarss_activated_yes'	=>		_('Yes'),
		), TRUE);
	}

    function _render_reset_tab($settings)
    {
        return $this->object->render_partial(
            'reset_tab',
            array(
                'reset_value'   => _('Reset all options to default settings'),
                'reset_warning' => _('Replace all existing options and gallery options with their default settings'),
                'reset_label'   => _('Reset settings'),
                'reset_confirmation' => _('Reset all options to default settings?\n\nChoose [Cancel] to Stop, [OK] to proceed.'),
                'show_uninstall'      => (!is_multisite() || wpmu_site_admin()),
                'uninstall_label'     => _('Deactivate / Uninstall NextGEN'),
                'check_uninstall_url' => menu_page_url('ngg_deactivator_check_uninstall', FALSE)
            ),
            TRUE
        );
    }



	/**
	 * Renders the thumbnai options tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_thumbnail_options_tab($settings)
	{
		return $this->render_partial('nextgen_other_options#thumbnail_options_tab', array(
			'thumbnail_dimensions_label'		=>	_('Default thumbnail dimensions:'),
			'thumbnail_dimensions_help'		=>	_('When generating thumbnails, what image dimensions do you desire?'),
			'thumbnail_dimensions_width'		=>	$settings->thumbwidth,
			'thumbnail_dimensions_height'		=>	$settings->thumbheight,
			'thumbnail_quality_label'		=>	_('Adjust Thumbnail Quality?'),
			'thumbnail_quality_help'		=>	_('When generating thumbnails, what image quality do you desire?'),
			'thumbnail_quality'				=>	$settings->thumbquality
		), TRUE);
	}

    /**
     * Passes control to NextGen-Deactivator->flush_cache() if the user requested it
     *
     * @return void
     */
    function _flush_nextgen_cache()
    {
        if (!isset($_POST['flush_cache'])) return;

        $cache = $this->object->get_registry()->get_utility('I_Cache');
        $cache->flush_galleries();
    }

    function _save_image_slugs($settings)
    {
        if (!isset($_POST['createslugs'])) return;

        global $wpdb;

        $total = array('images', 'gallery', 'album');

        // TODO: replace the album count with a datamapper provided tally
        $total['album']   = intval($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->nggalbum}`"));
        $total['gallery'] = $this->object->get_registry()->get_utility('I_Gallery_Mapper')->count();
        $total['images']  = $this->object->get_registry()->get_utility('I_Image_Mapper')->count();

        $messages = array(
            'album'   => __('Rebuild album structure : %s / %s albums', 'nggallery'),
            'gallery' => __('Rebuild gallery structure : %s / %s galleries', 'nggallery'),
            'images'  => __('Rebuild image structure : %s / %s images', 'nggallery'),
        );

        return $this->render_partial('permalinks_tab_rebuild_msg',
            array(
                'ajax_url' => add_query_arg('action', 'ngg_rebuild_unique_slugs', admin_url('admin-ajax.php')),
                'messages' => $messages,
                'total'    => $total
            ),
            FALSE
        );
    }

	/**
	 * Saves the lightbox library settings
	 * @param type $settings
	 */
	function _save_lightbox_library($settings)
	{
		// Ensure that a lightbox library was selected
		if (($id = $this->object->param('lightbox_library_id'))) {

			// Get the lightbox library mapper and find the library selected
			$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
			$library = $mapper->find($id, TRUE);

			// If a valid library, we have updated settings from the user, then
			// try saving the changes
			if ($library && (($params = $this->object->param('lightbox_library')))) {
				foreach ($params as $k=>$v) $library->$k = $v;
				$mapper->save($library);

				// If the requested changes weren't valid, add the validation
				// errors to the C_NextGen_Settings object
				if ($settings->is_invalid()) {
					foreach ($library->get_errors() as $property => $errs) {
						foreach ($errs as $error) $settings->add_error(
							$error, $property
						);;
					}
				}

				// The lightbox library update was successful.
				// Update C_NextGen_Settings
				else {
					$settings->thumbEffect = $library->name;
					$settings->thumbCode   = $library->code;
				}
			}
		}
	}

    /**
     * Handles changes required when the gallerypath setting changes: moves existing files & updates cached paths
     *
     * @param C_NextGen_Settings $settings
     * @param array $params
     * @return mixed
     */
    function _save_gallery_path($settings, $params)
    {
        if (isset($params['gallerypath']) && $params['gallerypath'] !== $settings->gallerypath)
        {
            $params['gallerypath'] = trailingslashit($params['gallerypath']);
            $dir = ABSPATH . $params['gallerypath'];

            if (file_exists($dir) && !is_dir($dir))
            {
                $error_msg = _('Your gallery path directory must not exist already');
                return array('message' => "<div class='error entity_errors'>{$error_msg}</div>");
            }

            // determine the parent directory so that we can make sure it exists
            $folders = explode(DIRECTORY_SEPARATOR, trim($dir, DIRECTORY_SEPARATOR));
            array_pop($folders);
            $parent = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $folders) . DIRECTORY_SEPARATOR;

            if (file_exists($dir) && !is_dir($dir))
            {
                $error_msg = _('Your gallery path must be in a directory');
                return array('message' => "<div class='error entity_errors'>{$error_msg}</div>");
            }

            // the 3rd mkdir() param makes it recursive
            if (!is_dir($parent))
                mkdir($parent, 0777, TRUE);

            // move our old directory under the new $parent
            rename (ABSPATH . $settings->gallerypath, $dir);

            // update galleries with their new path
            $settings->gallerypath = $params['gallerypath'];
            foreach ($this->get_registry()->get_utility('I_Gallery_Mapper')->find_all(TRUE) as $gallery) {
                $gallery->save();
            }
        }
    }












	/**
	 * Saves the contents of a stylesheet
	 */
	function _save_stylesheet_contents($css_file)
	{
		// Need to verify role
		if (($contents = $this->object->param('cssfile_contents')))
        {
			// Find filename
			$filename = path_join(TEMPLATEPATH, $css_file);
			$alt_filename = path_join(
				NGGALLERY_ABSPATH,
				implode(DIRECTORY_SEPARATOR, array('css', $css_file))
			);
			$found = FALSE;
			if (file_exists($filename)) {
				if (is_writable($filename)) $found = $filename;
			}
			elseif (file_exists($alt_filename)) {
				if (is_writable($alt_filename)) $found = $alt_filename;
			}

			// Write file contents
			if ($found)
            {
				$fp = fopen($found, 'w');
				fwrite($fp, $contents);
				fclose($fp);
			}
		}
	}

	/**
	 * Gets the CSS files available in the installation
	 * @return array
	 */
	function _get_cssfiles()
	{
		/** THIS FUNCTION WAS TAKEN FROM NGGLEGACY **/
		$cssfiles = array ();

		// Files in nggallery/css directory
		$plugin_root = NGGALLERY_ABSPATH . "css";

		$plugins_dir = @ dir($plugin_root);
		if ($plugins_dir) {
			while (($file = $plugins_dir->read()) !== false) {
				if (preg_match('|^\.+$|', $file))
					continue;
				if (is_dir($plugin_root.'/'.$file)) {
					$plugins_subdir = @ dir($plugin_root.'/'.$file);
					if ($plugins_subdir) {
						while (($subfile = $plugins_subdir->read()) !== false) {
							if (preg_match('|^\.+$|', $subfile))
								continue;
							if (preg_match('|\.css$|', $subfile))
								$plugin_files[] = "$file/$subfile";
						}
					}
				} else {
                    if ($file === 'default.css') { continue; }
					if (preg_match('|\.css$|', $file))
						$plugin_files[] = $file;
				}
			}
		}

		if ( !$plugins_dir || !$plugin_files )
			return $cssfiles;

		foreach ( $plugin_files as $plugin_file ) {
			if ( !is_readable("$plugin_root/$plugin_file"))
				continue;

			$plugin_data = $this->object->_get_cssfiles_data("$plugin_root/$plugin_file");

			if ( empty ($plugin_data['Name']) )
				continue;

			$cssfiles[plugin_basename($plugin_file)] = $plugin_data;
		}

		uasort($cssfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

		return $cssfiles;
	}


	/**
	 * Parses the CSS header
	 * @param string $plugin_file
	 * @return array
	 */
	function _get_cssfiles_data($plugin_file)
	{
		$plugin_data = implode('', file($plugin_file));
		preg_match("|CSS Name:(.*)|i", $plugin_data, $plugin_name);
		preg_match("|Description:(.*)|i", $plugin_data, $description);
		preg_match("|Author:(.*)|i", $plugin_data, $author_name);
		if (preg_match("|Version:(.*)|i", $plugin_data, $version))
			$version = trim($version[1]);
		else
			$version = '';

		$description = wptexturize(trim($description[1]));

		$name = trim($plugin_name[1]);
		$author = trim($author_name[1]);

		return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version );
	}
}
