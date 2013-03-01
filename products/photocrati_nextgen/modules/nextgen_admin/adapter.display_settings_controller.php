<?php

class A_Display_Settings_Controller extends Mixin
{
	/**
	 * Static resources required for the Display Settings page
	 */
	function enqueue_backend_resources()
	{
		$this->call_parent('enqueue_backend_resources');
		wp_enqueue_style('nextgen_display_settings_page', $this->get_static_url('nextgen_display_settings_page.css'));
		wp_enqueue_script(
			'nextgen_display_settings_page',
			$this->get_static_url('nextgen_display_settings_page.js'),
			array('jquery-ui-accordion')
		);
	}

	/**
	 * Returns an instance of the display type mapper
	 * @return type
	 */
	function get_mapper()
	{
		return $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
	}

	/**
	 * Renders the index action
	 */
	function index_action()
	{
		if (($token = $this->object->is_authorized_request('nextgen_edit_display_settings'))) {
			$messages = array();
			$display_type_tabs = array();

			// Retrieve all display types, get their settings form, and
			// process any changes submitted by the user
			$display_types = $this->object->get_mapper()->find_all();
			foreach ($display_types as $display_type) {

				// Get the settings form tab for the display type
				$display_type_tabs[] = $this->object->get_settings_form_tab(
					$display_type
				);

				// Process the form
				if ($this->object->is_post_request()) {

					// Save display type
					if (!$this->object->save_display_type($display_type)) {
						$messages[] = $this->object->show_errors_for(
							$display_type,
							TRUE
						);
					}
				}
			}

			// Render the view
			$this->render_partial('gallery_settings/display_settings_page', array(
				'page_heading'	=>	'NextGEN Display Settings',
				'tabs'			=>	$display_type_tabs,
				'messages'		=>	$messages,
				'form_header'	=>  $token->get_form_html()
			));
		}
		else echo('not authorized');
	}

	/**
	 * Saves any changes made to a display type
	 * @param $display_type
	 * @return boolean
	 */
	function save_display_type($display_type)
	{
		$retval = TRUE;

		if (($params = $this->object->param($display_type->name))) {
			foreach ($params as $k => $v) $display_type->settings[$k] = $v;
			$retval = $display_type->save();
		}

		return $retval;
	}

	/**
	 * Returns the accordion tab used to configure a particular display type
	 * @param C_Display_Type $display_type
	 */
	function get_settings_form_tab($display_type)
	{
		// Get the controller for this display type
		$display_type_controller = $this->object->get_registry()->get_utility(
			'I_Display_Type_Controller', $display_type->name
		);

		// Enqueue any static resources required
		$display_type_controller->enqueue_backend_resources($display_type);

		// Return the settings form for the display type
		return $this->render_partial('nextgen_gallery_display#gallery_settings/accordion_tab', array(
			'id'		=>	$display_type->name,
			'title'		=>	$display_type->title,
			'content'	=>	$display_type_controller->settings_action($display_type, TRUE)
		), TRUE);
	}
}