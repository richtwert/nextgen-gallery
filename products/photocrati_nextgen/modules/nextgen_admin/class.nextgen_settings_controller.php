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
}
