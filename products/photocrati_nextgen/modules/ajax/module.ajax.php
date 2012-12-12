<?php

/***
 {
		Module: photocrati-ajax,
		Depends: { photocrati-mvc }
 }
 */

define(
	'NEXTGEN_GALLERY_AJAX_URL',
	real_site_url('photocrati_ajax')
);

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
        $router->add_route(__CLASS__, 'C_Ajax_Controller', array(
            'uri'=>$router->routing_pattern('photocrati_ajax')
        ));
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
		wp_enqueue_script('photocrati_ajax', NEXTGEN_GALLERY_AJAX_URL.'/js');
	}
}

new M_Ajax();