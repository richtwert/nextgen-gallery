<?php

/***
{
		Module:		photocrati-nextgen_basic_slideshow,
		Depends:	{ photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

define(
	'PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW_JS_URL',
	PHOTOCRATI_GALLERY_PLUGIN_MODULE_URL.'/'.basename(dirname(__FILE__)).'/js'
);

define(
	'PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW',
	'photocrati-nextgen_basic_slideshow'
);

class M_NextGen_Basic_Slideshow extends C_Base_Module
{
	function define()
	{
		parent::define(
			PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW,
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

		// Provides alternative views for this display type
		// TODO: This needs to be moved to something more global in nature:
		// https://www.wrike.com/open.htm?id=8219830
//		$this->get_registry()->add_adapter(
//			'I_Display_Type_Controller',
//			'A_NextGen_Basic_Slideshow_Alternative_Views'
//		);
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
