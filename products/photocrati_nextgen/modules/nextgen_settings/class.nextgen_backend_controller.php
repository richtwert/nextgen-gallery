<?php

class C_NextGen_Backend_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Backend_Controller');
		$this->implement('I_NextGen_Backend_Controller');
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
 * Provides the default implementation for a NextGEN Admin Controller
 */
class Mixin_NextGen_Backend_Controller extends Mixin
{
	function enqueue_backend_resources()
	{
		// Enqueue JQuery UI
		wp_enqueue_script('jquery-ui-accordion');

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

		wp_enqueue_script(
            'nextgen_display_settings_page_placeholder_stub',
            $this->static_url('jquery.placeholder.min.js'),
            array('jquery'),
            '2.0.7',
            TRUE
        );

        wp_register_script('farbtastic', real_site_url('/wp-admin/js/farbtastic.js'), array('jquery'), '1.2');
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');

		wp_enqueue_script(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.js')
		);
		wp_enqueue_style(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.css')
		);
	}
}


/**
 * Enqueues backend resources whenever an MVC Controller action is executed
 */
class Hook_Enqueue_Backend_Resources extends Hook
{
	function enqueue_backend_resources()
	{
		if (preg_match("/_action$/", $this->method_called)) {
			$this->object->enqueue_backend_resources();
		}
	}
}
