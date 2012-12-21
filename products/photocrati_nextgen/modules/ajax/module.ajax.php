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

        $this->get_registry()
             ->get_utility('I_Router')
             ->add_pre_hook('serve_request', 'Adds MediaRSS routes', 'Hook_AJAX_Routes', 'add_routes');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
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
        wp_enqueue_script('photocrati_ajax', NEXTGEN_GALLERY_AJAX_JS_URL);
	}
}

new M_Ajax();