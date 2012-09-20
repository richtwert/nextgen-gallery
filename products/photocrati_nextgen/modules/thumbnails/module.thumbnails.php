<?php

/***
 {
	Module: photocrati-thumbnails
 }
 ***/

class M_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-thumbnails',
			'Thumbnails',
			'Adds support for thumbnails',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}


	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_NextGen_Backend_Controller',
			'A_Thumbnail_Dimension_Resources'
		);
	}
}

new M_Thumbnails();