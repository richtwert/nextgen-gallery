<?php

/***
{
		Module:		photocrati-nextgen_basic_slideshow,
		Depends:	{ photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

define(
	'NEXTGEN_GALLERY_BASIC_SLIDESHOW_JS_URL',
	NEXTGEN_GALLERY_MODULE_URL.'/'.basename(dirname(__FILE__)).'/js'
);

define(
	'NEXTGEN_GALLERY_BASIC_SLIDESHOW',
	'photocrati-nextgen_basic_slideshow'
);

class M_NextGen_Basic_Slideshow extends C_Base_Module
{
	function define()
	{
		parent::define(
			NEXTGEN_GALLERY_BASIC_SLIDESHOW,
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
		$this->get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_NextGen_Basic_Slideshow_Activation'
		);

		// Provides settings fields and frontend rendering
		$this->get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_Basic_Slideshow_Controller',
			$this->module_id
		);

		// Provides validation for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type',
			'A_NextGen_Basic_Slideshow'
		);

		// Provides default values for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_Slideshow_Mapper'
		);

		$this->get_registry()->add_adapter(
			'I_Routing_App',
			'A_NextGen_Basic_Slideshow_Urls'
		);
	}


	function _register_hooks()
	{
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	function enqueue_scripts()
	{
		wp_enqueue_script('swfobject');
	}
}

new M_NextGen_Basic_Slideshow();
