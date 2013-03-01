<?php

class C_NextGen_Admin_Page_Controller extends C_MVC_Controller
{
	static $_instances = array();

	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Admin_Page_Instance_Methods');
		$this->implement('I_NextGen_Admin_Page');
	}

	function initialize()
	{
		parent::initialize();
		$this->add_pre_hook(
			'index_action',
			'Enqueue Backend Resources',
			'Hook_NextGen_Admin_Page_Resources',
			'enqueue_backend_resources'
		);
	}
}

class Hook_NextGen_Admin_Page_Resources extends Hook
{
	function enqueue_backend_resources()
	{
		$this->object->enqueue_backend_resources();
	}
}


class Mixin_NextGen_Admin_Page_Instance_Methods extends Mixin
{
	/**
	 * Authorizes the request
	 */
	function is_authorized_request($privilege)
	{
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$retval = $sec_token = $security->get_request_token($privilege);
		$sec_actor = $security->get_current_actor();

		// Ensure that the user has permission to access this page
		if (!$sec_actor->is_allowed($privilege))
			$retval = FALSE;

		// Ensure that nonce is valid
		if ($this->object->is_post_request() && !$sec_token->check_current_request()) {
			$retval = FALSE;
		}

		return $retval;
	}

	function enqueue_backend_resources()
	{
		$this->object->enqueue_jquery_ui_theme();
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script(
            'nextgen_display_settings_page_placeholder_stub',
            $this->get_static_url('jquery.placeholder.min.js'),
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

	function enqueue_jquery_ui_theme()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		wp_enqueue_style(
			$settings->jquery_ui_theme,
			is_ssl() ?
				 str_replace('http:', 'https:', $settings->jquery_ui_theme_url) :
				 $settings->jquery_ui_theme_url,
			array(),
			$settings->jquery_ui_theme_version
		);
	}
}