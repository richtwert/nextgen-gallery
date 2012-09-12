<?php

/**
 * Enqueues resources needed for the NextGen Settings page
 */
class A_NextGen_Settings_Page_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Static resources for NextGen Settings page',
			__CLASS__,
			'enqueue_resources_for_nextgen_settings_page'
		);
	}

	function enqueue_resources_for_nextgen_settings_page()
	{
		wp_enqueue_script(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.js'),
			array('jquery-ui-accordion'),
			$this->module_version
		);

		wp_enqueue_style(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.css'),
			array(),
			$this->module_version
		);
	}
}