<?php
/***
{
	Module:		photocrati-nextgen_basic_imagebrowser,
	Depends:	{ photocrati-gallery_display, photocrati-thumbnails }
}
***/

define(
	'NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER',
	'photocrati-nextgen_basic_imagebrowser'
);

class M_NextGen_Basic_ImageBrowser extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_basic_imagebrowser',
			'NextGEN Basic ImageBrowser',
			'Provides the NextGEN Basic ImageBrowser Display Type',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Register adapters required for the NextGen Basic ImageBrowser
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
		  'I_Display_Type_Mapper',		'A_NextGen_Basic_ImageBrowser_Mapper'
		);

		// Add validation for the display type
		$this->get_registry()->add_adapter(
		  'I_Display_Type',			    'A_NextGen_Basic_ImageBrowser'
		);

		// Add activation routine
		$this->get_registry()->add_adapter(
		  'I_NextGen_Activator',	   'A_NextGen_Basic_ImageBrowser_Activation'
		);

		// Add rendering logic
		$this->get_registry()->add_adapter(
		  'I_Display_Type_Controller', 'A_NextGen_Basic_ImageBrowser_Controller',
		  $this->module_id
		);
	}
}

new M_NextGen_Basic_ImageBrowser();