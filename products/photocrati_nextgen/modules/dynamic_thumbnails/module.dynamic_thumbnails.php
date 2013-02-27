<?php

/***
 {
	Module: photocrati-dynamic-thumbnails,
	Depends: { photocrati-nextgen_data }
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
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_Dynamic_Thumbnail_Routes');
		$this->get_registry()->add_adapter('I_GalleryStorage_Driver', 'A_Dynamic_Thumbnails_Storage_Driver');
		$this->get_registry()->add_adater('I_Settings_Manager', 'A_Dynamic_Thumbnail_Settings');
	}

	function _register_utilities()
	{
        $this->get_registry()->add_utility('I_Dynamic_Thumbnails_Manager', 'C_Dynamic_Thumbnails_Manager');
        $this->get_registry()->add_utility('I_Dynamic_Thumbnails_Controller', 'C_Dynamic_Thumbnails_Controller');
	}

}

new M_Dynamic_Thumbnails();
