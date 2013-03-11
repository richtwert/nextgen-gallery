<?php

/***
{
		Module:		photocrati-nextgen_basic_slideshow,
		Depends:	{ photocrati-nextgen_gallery_display }
}
 ***/

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

	function initialize()
	{
		parent::initialize();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG,
			NEXTGEN_GALLERY_BASIC_SLIDESHOW
		);
	}


	function _register_adapters()
	{
		// Installs the display type
		$this->get_registry()->add_adapter(
			'I_Installer',
			'A_NextGen_Basic_Slideshow_Installer'
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

		// Adds url handling for NextGen Basic Slideshow
		$this->get_registry()->add_adapter(
			'I_Routing_App',
			'A_NextGen_Basic_Slideshow_Urls'
		);

		// Adds the display settings form
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_Slideshow_Form',
			$this->module_id
		);
	}


	function _register_hooks()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		add_shortcode('slideshow',		 array(&$this, 'render_slideshow'));
		add_shortcode('nggslideshow',	 array(&$this, 'render_slideshow'));
	}

	function enqueue_scripts()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		wp_register_script(
			'jquery.cycle',
			$router->get_static_url('/jquery.cycle.min.js'),
			array('jquery')
		);
	}

	function render_slideshow($params, $inner_content=NULL)
	{
		$params['gallery_ids']    = $this->_get_param('id', NULL, $params);
        $params['display_type']   = $this->_get_param('display_type', 'photocrati-nextgen_basic_slideshow', $params);
        $params['gallery_width']  = $this->_get_param('w', NULL, $params);
        $params['gallery_height'] = $this->_get_param('h', NULL, $params);
        unset($params['id'], $params['w'], $params['h']);

		$renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
	}
}

new M_NextGen_Basic_Slideshow();
