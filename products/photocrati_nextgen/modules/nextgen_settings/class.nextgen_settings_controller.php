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
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');

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
			_('Lightbox Effect') =>$this->object->_render_lightbox_library_tab($settings),
			_('Image Sorting')	 =>$this->object->_render_image_sorting_tab($settings),
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
	 * Renders the image sorting tab
	 * @param C_NextGen_Settings $settings
	 * @return string
	 */
	function _render_image_sorting_tab($settings)
	{
		return $this->render_partial('image_sorting_tab', array(
			'sorting_order_label'		=>	_('Sorting Order'),
			'sorting_order_options'		=>	array(
				'Custom'				=>	'sortorder',
				'Image ID'				=>	'pid',
				'Filename'				=>	'filename',
				'Alt/Title Text'		=>	'alttext',
				'Date/Time'				=>	'imagedate'
			),
			'sorting_order'				=>	$settings->galSort,
			'sorting_direction_label'	=>	_('Sorting Direction'),
			'sorting_direction_options'	=>	array(
				'Ascending'				=>	'ASC',
				'Descending'			=>	'DESC'
			),
			'sorting_direction'			=>	$settings->galSortDir
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
		$mapper = $this->object->_get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$libs = $mapper->find_all(array(), TRUE);

		// Render tab
		return $this->render_partial('lightbox_library_tab', array(
			'libs'		=>	$libs,
			'id_field'	=>	$mapper->get_primary_key_column(),
			'selected'	=>	$settings->thumbEffect,
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
			$mapper = $this->object->_get_registry()->get_utility('I_Lightbox_Library_Mapper');
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
}