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
        return;

		$router = $this->get_registry()->get_utility('I_Router');

        $url = $this->get_registry()
                    ->get_utility('I_Display_Type_Controller')
                    ->add_parameter('photocrati_ajax', TRUE, NULL, site_url());

        define('NEXTGEN_GALLERY_AJAX_URL', $url);

        $router->add_route(
            __CLASS__,
            'C_Ajax_Controller',
            array('uri' => $router->routing_pattern($url))
        );

        // define ajax-js url
        $url = $this->get_registry()
                    ->get_utility('I_Display_Type_Controller')
                    ->add_parameter('action', 'js', NULL, NEXTGEN_GALLERY_AJAX_URL);

        define('NEXTGEN_GALLERY_AJAX_JS_URL', $url);
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
		// wp_enqueue_script('photocrati_ajax', NEXTGEN_GALLERY_AJAX_JS_URL);
	}
}

new M_Ajax();