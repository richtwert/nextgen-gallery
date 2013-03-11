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

	function get_page_title()
	{
		return 'Gallery Settings';
	}
}