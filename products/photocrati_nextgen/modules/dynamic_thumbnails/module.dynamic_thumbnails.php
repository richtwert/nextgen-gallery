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
		
		// XXX temporary hack, why is this not included automatically?
		include_once(dirname(__FILE__) . '/mixin.dynamic_thumbnails_manager.php');
		
		$this->add_mixin('Mixin_Dynamic_Thumnbails_Manager');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->_add_routes();
		
#		var_dump($this->object->get_params_from_name('portfolio-005-nggid014-ngg0dyn-120x90x100-00f0w011c011r010.jpg'));
#		var_dump($this->object->get_image_name(4, array('width' => 120, 'height' => '90')));
	}

	/**
	 * Adds a route for the AJAX controller
	 */
	function _add_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$router->add_route(__CLASS__, 'C_Dynamic_Thumbnails_Controller', array(
				'uri'=>$router->routing_pattern($this->object->get_route_name(), '*')
		));
	}
}

new M_Dynamic_Thumbnails();
