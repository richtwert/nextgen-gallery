<?php

class C_Display_Settings_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Display_Settings_Controller');
		$this->implement('I_Display_Settings_Controller');
	}

	/**
	 * Gets an instance of the display settings controller
	 * @param string $context
	 */
	static function get_instance($context=FALSE)
	{
		if (!(isset(self::$_instances[$context]))) {
			self::$_instances[$context] = new C_Display_Settings_Controller($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provides instance methods for the display settings controller
 */
class Mixin_Display_Settings_Controller extends Mixin
{
	function index()
	{
		$display_type_tabs = array();

		// Retrieve all display types. I'm currently retrieving all as models,
		// as set_defaults() is NOT called otherwise. If there are validation
		// errors too, we need to display them.
		// TODO: Figure out a better way to get validation errors. Models are
		// too expensive to use with collections.
		$mapper = $this->object->_get_registry()->get_utility('I_Display_Type_Mapper');
		$display_types = $mapper->find_all(array(), TRUE);
		$messages = array();

		// For each display type, get the settings pages
		foreach ($display_types as $display_type) {
			$display_type_controller = $this->object->_get_registry()->get_utility(
				'I_Display_Type_Controller', $display_type->name
			);
			$display_type_controller->enqueue_backend_resources($display_type);
			$display_type_tabs[] = $display_type_controller->settings(
				$display_type, TRUE
			);

			// Process the form
			if ($this->object->is_post_request()) {
				if (($params = $this->object->param($display_type->name))) {
					foreach ($params as $k => $v) $display_type->settings[$k] = $v;
					if ($display_type->save()) {
						$messages[] = $this->object->show_success_for(
							$display_type,
							$display_type->title,
							TRUE
						);
					}
					else {
						$messages[] = $this->object->show_errors_for(
							$display_type,
							TRUE
						);
					}
				}
			}
		}

		// Render the view
		$this->render_partial('display_settings_page', array(
			'page_heading'	=>	'NextGEN Display Settings',
			'tabs'			=>	$display_type_tabs,
			'messages'		=>	$messages
		));
	}
}