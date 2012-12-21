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

        $this->get_registry()
             ->get_utility('I_Router')
             ->add_pre_hook('serve_request', 'Adds MediaRSS routes', 'Hook_Dynamic_Thumbnails_Routes', 'add_routes');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
	}
	
	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_GalleryStorage_Driver', 'A_Dynamic_Thumbnails_Storage_Driver');
	}

	function _register_utilities()
	{
        $this->get_registry()->add_utility('I_Dynamic_Thumbnails_Manager', 'C_Dynamic_Thumbnails_Manager');
	}

}

new M_Dynamic_Thumbnails();
