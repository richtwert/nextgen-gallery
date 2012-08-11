<?php

/**
 * Provides a wp-admin page to manage NextGEN Settings
 */
class C_NextGen_Settings_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_NextGen_Settings_Controller');
		$this->add_mixin('Mixin_Lightbox_Library_Tab');
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

		$this->render_partial('nextgen_settings_page', array(
			'page_heading'	=>	$this->object->_get_options_page_heading(),
			'tabs'		=>	$this->object->_get_tabs($settings),
		));
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
			_('Lightbox Effect') =>$this->object->_render_lightbox_library_tab($settings)
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
}

/**
 * Provides a tab to configure the lightbox effect used
 */
class Mixin_Lightbox_Library_Tab extends Mixin
{
	function _render_lightbox_library_tab($settings)
	{
		// Get the library mapper. We'll need this from now on
		$mapper = $this->object->_get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$message = FALSE;

		// Save the selected lightbox library
		if ($this->object->is_post_request() && (($id = $this->object->param('id')))) {
			$library = $mapper->find($id, TRUE);
			if ($library) {
				// Update library
				if (($params = $this->object->param('lightbox_library'))) {
					foreach ($params as $k => $v) $library->$k = $v;
					$mapper->save($library);
					if ($library->is_invalid())
						$message = $this->object->show_errors_for($library, TRUE);
					else
						$message = $this->object->show_success_for($library, 'Lightbox settings', TRUE);
				}

				// Set default lightbox library
				$settings->thumbEffect = $library->name;
				$settings->thumbCode   = $library->code;
				$settings->save();
			}
		}
		// Find all lightbox effect libraries. We're retrieving them as models
		// which is NOT the best idea, but currently only models have the
		// set_defaults() method executed for them.
		// TODO: Adjust datamapper drivers to call a set_defaults() method
		// from the convert_to_entity() method
		$libs = $mapper->find_all(array(), TRUE);

		// Render tab
		return $this->render_partial('lightbox_library_tab', array(
			'libs'		=>	$libs,
			'id_field'	=>	$mapper->get_primary_key_column(),
			'selected'	=>	$settings->thumbEffect,
			'message'	=>	$message
		), TRUE);
	}
}