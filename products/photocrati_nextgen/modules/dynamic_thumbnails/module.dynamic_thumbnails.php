<?php

/***
 {
	Module: photocrati-dynamic-thumbnails
 }
 ***/

class M_Dynamic_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-dynamic-thumbnails',
			'Dynamic Thumbnails',
			'Adds support for dynamic thumbnails',
			'0.1',
			'http://www.nextgen-gallery.com',
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
        $router->add_route(__CLASS__, 'C_Dynamic_Thumbnails_Controller', array(
            'uri'=>$router->routing_pattern('nextgen_image', '*')
        ));
	}
}

new M_Dynamic_Thumbnails();
