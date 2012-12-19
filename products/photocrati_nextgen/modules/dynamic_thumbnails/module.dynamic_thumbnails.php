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
	
	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_GalleryStorage_Driver', 'A_Dynamic_Thumbnails_Storage_Driver');
	}

	function _register_utilities()
	{
  	$this->get_registry()->add_utility('I_Dynamic_Thumbnails_Manager', 'C_Dynamic_Thumbnails_Manager');
	}

	/**
	 * Adds a route for the AJAX controller
	 */
	function _add_routes()
	{
        return;

		$router = $this->get_registry()->get_utility('I_Router');
		$dynthumbs = $this->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
		$router->add_route(__CLASS__, 'C_Dynamic_Thumbnails_Controller', array(
				'uri'=>$router->routing_pattern($dynthumbs->get_route_name(), '*')
		));
	}
}

new M_Dynamic_Thumbnails();
