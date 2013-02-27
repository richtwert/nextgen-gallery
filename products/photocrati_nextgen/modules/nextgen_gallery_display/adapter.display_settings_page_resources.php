<?php

class A_Display_Settings_Page_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueue static resources for the Display Settings page',
			__CLASS__,
			'enqueue_display_settings_resources'
		);
	}

	/**
	 * Enqueues static resources needed for the Display Settings page
	 */
	function enqueue_display_settings_resources()
	{
		wp_enqueue_script(
			'nextgen_display_settings_page',
			$this->static_url('nextgen_display_settings_page.js'),
			array('jquery-ui-accordion'),
			$this->module_version
		);

		wp_enqueue_style(
			'nextgen_display_settings_page',
			$this->static_url('nextgen_display_settings_page.css')
		);
	}
}