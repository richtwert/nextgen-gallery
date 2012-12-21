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
        $this->_add_routes();
	}


	/**
	 * Adds a route for the AJAX controller
	 */
	function _add_routes()
	{
        $router = $this->get_registry()->get_utility('I_Router');
        $app = $router->create_app();

        // TODO: fix this for wordpress installations in a sub-folder
        $ajax_url = '/photocrati_ajax';
        $js_url = $ajax_url . '/js';

        define('NEXTGEN_GALLERY_AJAX_URL', $ajax_url);
        define('NEXTGEN_GALLERY_AJAX_JS_URL', $js_url);

        $app->route(
            array($js_url),
            array(
                'controller' => 'C_Ajax_Controller',
                'action'  => 'js',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );

        $app->route(
            array($ajax_url),
            array(
                'controller' => 'C_Ajax_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET', 'POST')
            )
        );
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