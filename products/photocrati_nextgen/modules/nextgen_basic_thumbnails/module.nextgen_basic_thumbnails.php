<?php

/***
{
		Module:		photocrati-nextgen_basic_thumbnails,
		Depends:	{ photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

define(
	'PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS',
	'photocrati-nextgen_basic_thumbnails'
);

class M_NextGen_Basic_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS,
			'NextGen Basic Thumbnails',
			'Provides a thumbnail gallery for NextGEN Gallery',
			'1.9.6',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}


	function _register_adapters()
	{
		// Installs the display type
		$this->get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_NextGen_Basic_Thumbnails_Activation'
		);

		// Provides settings fields and frontend rendering
		$this->get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_Basic_Thumbnails_Controller',
			$this->module_id
		);

		// Provides validation for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type',
			'A_NextGen_Basic_Thumbnails'
		);

		// Provides default values for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_Thumbnails_Mapper'
		);

		// Provides AJAX pagination actions required by the display type
        $this->get_registry()->add_adapter(
            'I_Ajax_Controller',
            'A_Ajax_Pagination_Actions'
        );
	}
}

new M_NextGen_Basic_Thumbnails();
