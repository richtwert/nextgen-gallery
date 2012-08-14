<?php

/***
{
		Module:		photocrati-nextgen_basic_thumbnails,
		Depends:	{ photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

define(
	'PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS_JS_URL',
	PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js'
);

define(
	'PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS',
	'photocrati-nextgen_basic_thumbnails'
);

class M_NextGen_Basic_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_basic_thumbnails',
			'NextGen Basic Thumbnails',
			'Provides a thumbnail gallery for NextGEN Gallery',
			'1.9.5',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}


	function _register_adapters()
	{
		// Installs the display type
		$this->_get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_NextGen_Basic_Thumbnails_Activation'
		);

		// Provides settings fields and frontend rendering
		$this->_get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_Basic_Thumbnails_Controller',
			$this->module_id
		);

		// Provides validation for the display type
		$this->_get_registry()->add_adapter(
			'I_Display_Type',
			'A_NextGen_Basic_Thumbnails'
		);
	}
}

new M_NextGen_Basic_Thumbnails();