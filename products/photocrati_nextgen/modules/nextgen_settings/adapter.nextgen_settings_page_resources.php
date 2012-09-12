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
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');

		wp_enqueue_script(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.js'),
			array('jquery-ui-accordion'),
			$this->module_version
		);

		// There are many jQuery UI themes available via Google's CDN:
		// See: http://stackoverflow.com/questions/820412/downloading-jquery-css-from-googles-cdn
		wp_enqueue_style(
			PHOTOCRATI_GALLERY_JQUERY_UI_THEME,
			is_ssl() ?
				 str_replace('http:', 'https:', PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL) :
				 PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL,
			array(),
			PHOTOCRATI_GALLERY_JQUERY_UI_THEME_VERSION
		);

		wp_enqueue_style(
			'nextgen_settings_page',
			$this->static_url('nextgen_settings_page.css'),
			array(),
			$this->module_version
		);
	}
}