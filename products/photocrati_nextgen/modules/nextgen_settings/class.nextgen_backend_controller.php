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
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		wp_enqueue_style(
			$settings->jquery_ui_theme,
			is_ssl() ?
				 str_replace('http:', 'https:', $settings->jquery_ui_theme_url()) :
				 $settings->jquery_ui_theme_url(),
			array(),
			$settings->jquery_ui_theme_version
		);

		wp_enqueue_script(
            'nextgen_display_settings_page_placeholder_stub',
            $this->static_url('jquery.placeholder.min.js'),
            array('jquery'),
            '2.0.7',
            TRUE
        );
		wp_register_script('iris', $this->get_router()->get_url('/wp-admin/js/iris.min.js', FALSE), array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'));
		wp_register_script('wp-color-picker', $this->get_router()->get_url('/wp-admin/js/color-picker.js', FALSE), array('iris'));
		wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array(
			'clear' => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick' => __( 'Select Color' ),
			'current' => __( 'Current Color' ),
		));
		wp_enqueue_script(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.js'),
            array('wp-color-picker')
		);
		wp_enqueue_style(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.css'),
            array('wp-color-picker')
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
