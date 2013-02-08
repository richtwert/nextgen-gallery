<?php

class C_Display_Settings_Controller extends C_NextGen_Backend_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Display_Settings_Controller');
		$this->implement('I_Display_Settings_Controller');
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
 * Provides instance methods for the display settings controller
 */
class Mixin_Display_Settings_Controller extends Mixin
{
	function index_action()
	{
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_token = $security->get_request_token('nextgen_edit_display_settings');
		$sec_actor = $security->get_current_actor();
		
		if (!$sec_actor->is_allowed('nextgen_edit_display_settings'))
		{
			echo __('No permission.', 'nggallery');
			
			return;
		}

		// Enqueue resources
		$this->enqueue_backend_resources();

		$display_type_tabs = array();

		// Retrieve all display types. I'm currently retrieving all as models,
		// as set_defaults() is NOT called otherwise. If there are validation
		// errors too, we need to display them.
		// TODO: Figure out a better way to get validation errors. Models are
		// too expensive to use with collections.
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		$display_types = $mapper->find_all(array(), TRUE);
		$messages = array();

		// For each display type, get the settings pages
		foreach ($display_types as $display_type) {

			// Process the form
			if ($this->object->is_post_request()) {
				if (!$sec_token->check_current_request()) {
					$messages[] = '<div class="entity_errors">' . __('The request has expired. Please refresh the page.', 'nggallery');
				}
				elseif (($params = $this->object->param($display_type->name))) {
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

			// Display the tab
			$display_type_controller = $this->object->get_registry()->get_utility(
				'I_Display_Type_Controller', $display_type->name
			);
			$display_type_controller->enqueue_backend_resources($display_type);
			$display_type_tabs[] = $this->render_partial('accordion_tab', array(
				'id'		=>	$display_type->name,
				'title'		=>	$display_type->title,
				'content'	=>	$display_type_controller->settings_action($display_type, TRUE)
			), TRUE);
		}

		// Render the view
		$this->render_partial('display_settings_page', array(
			'page_heading'	=>	'NextGEN Display Settings',
			'tabs'			=>	$display_type_tabs,
			'messages'		=>	$messages,
			'form_header' => $sec_token->get_form_html()
		));
	}
}
