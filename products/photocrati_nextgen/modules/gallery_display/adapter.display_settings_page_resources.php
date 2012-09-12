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
        wp_enqueue_script(
            'nextgen_display_settings_page_placeholder_stub',
            $this->static_url('jquery.placeholder.min.js'),
            array('jquery'),
            '2.0.7',
            TRUE
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
			'nextgen_display_settings_page',
			$this->static_url('nextgen_display_settings_page.css')
		);

        wp_enqueue_script('farbtastic');
        wp_enqueue_style('farbtastic');
	}
}