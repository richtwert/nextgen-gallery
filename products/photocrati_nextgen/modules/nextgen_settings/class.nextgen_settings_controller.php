<?php

/**
 * Provides a wp-admin page to manage NextGEN Settings
 */
class C_NextGen_Settings_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Settings_Controller');
		$this->implement('I_NextGen_Settings_Controller');
	}

	/**
	 * Gets an instance of the controller
	 * @param string $context
	 * @return C_NextGen_Settings_Controller
	 */
	static function get_instance($context=FALSE)
	{
		$klass = get_class();
		if (!isset(self::$_instances[$context])) {
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
	/**
	 * Returns the contents of the "Options" page
	 */
	function index()
	{
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		// Is this post a request? If so, process the request
		if ($this->is_post_request()) {
			$view_params = $this->object->_process_post_request($settings);
		}

		// Set other view params
		$view_params['page_heading'] = $this->object->_get_options_page_heading();
		$view_params['tabs']		 = $this->object->_get_tabs($settings);

		// Render view
		$this->render_partial('nextgen_settings_page', $view_params);
	}


	/**
	 * Processes the POST request
	 * @param C_NextGen_Settings $settings
	 */
	function _process_post_request($settings)
	{
		$retval = array();

		// Do we have sufficient data to continue?
		if (($params = $this->object->param('settings'))) {

			// Try saving the settings
			foreach ($params as $k=>$v) $settings->$k = $v;

			// Save lightbox effects settings
			if ($settings->is_valid()) {
				$this->object->_save_lightbox_library($settings);
			}

			// Save the changes made to the settings
			if ($settings->save()) {
				$retval['message'] = $this->object->show_success_for(
					$settings, 'NextGEN Gallery Settings', TRUE
				);
			}

			// Save failed. Display validation errors
			else {
				$retval['message'] = $this->object->show_errors_for(
					$settings, TRUE
				);
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
			_('Lightbox Effect')		=> $this->object->_render_lightbox_library_tab($settings),
			_('Watermarks')				=> $this->object->_render_watermarks_tab($settings),
			_('Custom Styling')			=> $this->object->_render_custom_styling_tab($settings),
			_('Roles / Capabilities')	=> $this->object->_render_roles_tab($settings)
		);

		if (is_multisite()) {
			$tabs['Multisite Options'] = $this->object->_render_multisite_options_tab($settings);
		}

		return $tabs;
	}

	/**
	 * Renders a form to managing NextGEN Multisite Settings
	 */
	function _render_multisite_options_tab($settings)
	{
		echo 'Multisite Options go here';
	}


	/**
	 * Renders the custom styling tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_custom_styling_tab($settings)
	{
		$view = path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
			'admin', 'style.php'
		)));
		ob_start();
		include_once($view);
		nggallery_admin_style();
		$retval = ob_get_contents();
		ob_end_clean();
		return $retval;
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


	function _render_watermarks_tab($settings)
	{
		return $this->render_partial('watermarks_tab', array(
			'notice'					=>	_('Please note : You can only activate the watermark under -> Manage Gallery . This action cannot be undone.'),
			'watermark_source_label'	=>	_('How will you generate a watermark?'),
			'watermark_sources'			=>	$this->object->_get_watermark_sources(),
			'watermark_fields'			=>	$this->object->_get_watermark_source_fields($settings),
			'watermark_source'			=>	$settings->wmType,
			'position_label'			=>	_('Position:'),
			'position'					=>	$settings->wmPos,
			'offset_label'				=>	_('Offset:'),
			'offset_x'					=>	$settings->wmXpos,
			'offset_y'					=>	$settings->wmYpos,
			'hidden_label'				=>	_('(Show Customization Options)'),
			'active_label'				=>	_('(Hide Customization Options)')
		), TRUE);
	}

	/**
	 * Renders the global options tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_image_options_tab($settings)
	{
		return $this->render_partial('image_options_tab', array(
			'gallery_path_label'			=>	_('Where would you like galleries stored?'),
			'gallery_path_help'				=>	_('This is the default path for all galleries'),
			'gallery_path'					=>	$settings->gallerypath,
			'delete_image_files_label'		=>	_('Delete Image Files?'),
			'delete_image_files_help'		=>	_('When enabled, image files will be removed after a Gallery has been deleted'),
			'delete_image_files'			=>	$settings->deleteImg,
			'show_related_images_label'		=>	_('Show Related Images on Posts?'),
			'show_related_images_help'		=>	_('When enabled, related images will be appended to each post'),
			'show_related_images'			=>	$settings->activateTags,
			'related_images_hidden_label'	=>	_('(Show Customization Settings)'),
			'related_images_active_label'	=>	_('(Hide Customization Settings)'),
			'match_related_images_label'	=>	_('How should related images be match?'),
			'match_related_images'			=>	$settings->appendType,
			'match_related_image_options'	=>	$this->object->_get_related_image_match_options(),
			'max_related_images_label'		=>	_('Maximum # of related images to display'),
			'max_related_images'			=>	$settings->maxImages,
			'sorting_order_label'			=>	_("What's the default sorting method?"),
			'sorting_order_options'			=>	$this->object->_get_image_sorting_options(),
			'sorting_order'					=>	$settings->galSort,
			'sorting_direction_label'		=>	_('Sort in what direction?'),
			'sorting_direction_options'		=>	$this->object->_get_sorting_direction_options(),
			'sorting_direction'				=>	$settings->galSortDir,
			'automatic_resize_label'		=>	'Automatically resize images after upload',
			'automatic_resize_help'			=>	'It is recommended that your images be resized to be web friendly',
			'automatic_resize'				=>	$settings->imgAutoResize,
			'resize_images_label'			=>	_('What should images be resized to?'),
			'resize_images_help'			=>	_('After images are uploaded, they will be resized to the above dimensions and quality'),
			'resized_image_width_label'		=>	_('Width:'),
			'resized_image_height_label'	=>	_('Height:'),
			'resized_image_quality_label'	=>	_('Quality:'),
			'resized_image_width'			=>	$settings->imgWidth,
			'resized_image_height'			=>  $settings->imgHeight,
			'resized_image_quality'			=>	$settings->imgQuality,
			'backup_images_label'			=>	_('Backup the original images?'),
			'backup_images_yes_label'		=>	_('Yes'),
			'backup_images_no_label'		=>	_('No'),
			'backup_images'					=>	$settings->imgBackup,
			'thumbnail_quality_label'		=>	_('Adjust Thumbnail Quality?'),
			'thumbnail_quality_help'		=>	_('When generating thumbnails, what image quality do you desire?'),
			'thumbnail_quality'				=>	$settings->thumbquality
		), TRUE);
	}


	/**
	 * Renders the lightbox library settings tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_lightbox_library_tab($settings)
	{
		// Find all lightbox effect libraries. We're retrieving them as models
		// which is NOT the best idea, but currently only models have the
		// set_defaults() method executed for them.
		// TODO: Adjust datamapper drivers to call a set_defaults() method
		// from the convert_to_entity() method
		$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$libs = $mapper->find_all(array(), TRUE);

		// Render tab
		return $this->render_partial('lightbox_library_tab', array(
			'lightbox_library_label'	=>	_('What effect would you like to use?'),
			'libs'						=>	$libs,
			'id_field'					=>	$mapper->get_primary_key_column(),
			'selected'					=>	$settings->thumbEffect,
		), TRUE);
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
	 * Returns the options available for sorting images
	 * @return array
	 */
	function _get_image_sorting_options()
	{
		return array(
			'Custom'					=>	'sortorder',
			'Image ID'					=>	'pid',
			'Filename'					=>	'filename',
			'Alt/Title Text'			=>	'alttext',
			'Date/Time'					=>	'imagedate'
		);
	}


	/**
	 * Returns the options available for sorting directions
	 * @return array
	 */
	function _get_sorting_direction_options()
	{
		return array(
			'Ascending'					=>	'ASC',
			'Descending'				=>	'DESC'
		);
	}


	/**
	 * Returns the options available for matching related images
	 */
	function _get_related_image_match_options()
	{
		return array(
			'Categories'				=>	'category',
			'Tags'						=>	'tags'
		);
	}

	/**
	 * Gets watermark sources, along with their respective fields
	 * @param C_NextGen_Settings $settings
	 * @return array
	 */
	function _get_watermark_sources()
	{
		// We do this so that an adapter can add new sources
		return array(
			'Using an Image'	=>	'image',
			'Using Text'		=>	'text',
		);
	}


	function _get_watermark_source_fields($settings)
	{
		$retval = array();
		foreach ($this->object->_get_watermark_sources() as $label => $value) {
			$method = "_render_watermark_{$value}_fields";
			$retval[$value] = $this->object->call_method($method, array($settings));
		}
		return $retval;
	}

	/**
	 * Gets all fonts installed for watermarking
	 * @return array
	 */
	function _get_watermark_fonts()
	{
		$retval = array();
		foreach (scandir(path_join(NGGALLERY_ABSPATH, 'fonts')) as $filename) {
			if (strpos($filename, '.') === 0) continue;
			else $retval[] = $filename;
		}
		return $retval;
	}


	/**
	 * Render fields that are needed when 'image' is selected as a watermark
	 * source
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_watermark_image_fields($settings)
	{
		return $this->object->render_partial('watermark_image_fields', array(
			'image_url_label'			=>	_('Image URL:'),
			'watermark_image_url'		=>	$settings->wmPath,
		), TRUE);
	}

	/**
	 * Render fields that are needed when 'text is selected as a watermark
	 * source
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_watermark_text_fields($settings)
	{
		return $this->object->render_partial('watermark_text_fields', array(
			'fonts'						=>	$this->object->_get_watermark_fonts($settings),
			'font_family_label'			=>	_('Font Family:'),
			'font_family'				=>	$settings->wmFont,
			'font_size_label'			=>	_('Font Size:'),
			'font_size'					=>	$settings->wmSize,
			'font_color_label'			=>	_('Font Color:'),
			'font_color'				=>	strpos($settings->wmColor, '#') === 0 ?
											$settings->wmColor : "#{$settings->wmColor}",
			'watermark_text_label'		=>	_('Text:'),
			'watermark_text'			=>	$settings->wmText,
			'opacity_label'				=>	_('Opacity:'),
			'opacity'					=>	$settings->wmOpaque,
		), TRUE);
	}

}