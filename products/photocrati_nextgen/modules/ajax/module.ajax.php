<?php

/*
 {
		Module: photocrati-ajax,
		Depends: { photocrati-mvc }
 }
 */
class M_Ajax extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-ajax',
			'AJAX',
			'Provides AJAX functionality',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_Ajax_Routes');
		$this->get_registry()->add_adapter('I_Settings_Manager', 'A_Ajax_Settings');
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Ajax_Controller', 'C_Ajax_Controller');

	}

	/**
	 * Hooks into the WordPress framework
	 */
	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_scripts'));
		add_action('admin_init', array(&$this, 'enqueue_scripts'));
	}


	/**
	 * Loads a single script to provide the photocrati_ajax_url to the web browser
	 */
	function enqueue_scripts()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		wp_register_script('photocrati_ajax', $settings->ajax_js_url, array(), NULL);
        wp_enqueue_script('photocrati_ajax');
	}

    function set_file_list()
    {
        return array(
            'adapter.ajax_routes.php',
            'adapter.ajax_settings.php',
            'class.ajax_controller.php',
            'interface.ajax_controller.php',
            'module.ajax.php'
        );
    }
}

new M_Ajax();