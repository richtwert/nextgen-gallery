<?php

/***
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

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$router = $this->get_registry()->get_utility('I_Router');
		define('NEXTGEN_GALLERY_AJAX_URL', $router->get_url('/photocrati_ajax/'));
		define('NEXTGEN_GALLERY_AJAX_JS_URL', $router->get_url('/photocrati_ajax/js'));
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_Ajax_Routes');
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
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		add_action('admin_init', array(&$this, 'enqueue_scripts'));
	}


	/**
	 * Loads a single script to provide the photocrati_ajax_url to the web browser
	 */
	function enqueue_scripts()
	{
        wp_enqueue_script('photocrati_ajax', NEXTGEN_GALLERY_AJAX_JS_URL, array(), NULL);
	}
}

new M_Ajax();