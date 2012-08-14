<?php

/***
	{
		Module: photocrati-gallery_display,
		Depends: { photocrati-lazy_resources }
	}
***/

class M_Gallery_Display extends C_Base_Module
{
	var $page_name = 'ngg_display_settings';

	function define()
	{
		parent::define(
			'photocrati-gallery_display',
			'Gallery Display',
			'Provides the ability to display gallery of images',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);

		$this->add_mixin('Mixin_Render_Display_Type');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->controller = $this->_get_registry()->get_utility('I_Display_Settings_Controller');
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
		$this->_get_registry()->add_utility(
			'I_Display_Settings_Controller',
			'C_Display_Settings_Controller'
		);

		// This utility provides a controller to render the settings form
		// for a display type, or render the front-end of a display type
		$this->_get_registry()->add_utility(
			'I_Display_Type_Controller',
			'C_Display_Type_Controller'
		);

		// This utility provides a datamapper for Display Types
		$this->_get_registry()->add_utility(
			'I_Display_Type_Mapper',
			'C_Display_Type_Mapper'
		);

		// This utility provides a datamapper for Displayed Galleries. A
		// displayed gallery is the association between some entities (images
		//or galleries) and a display type
		$this->_get_registry()->add_utility(
			'I_Displayed_Gallery_Mapper',
			'C_Displayed_Gallery_Mapper'
		);
	}


	/**
	 * Registers adapters required for this module
	 */
	function _register_adapters()
	{
		// Provides factory methods for creating display type and
		// displayed gallery instances
		$this->_get_registry()->add_adapter(
			'I_Component_Factory', 'A_Gallery_Display_Factory'
		);
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
		// Add the display settings page to wp-admin
		add_action('admin_menu', array(&$this, 'add_display_settings_page'), 999);

		// Enqueues static resources required
		if (is_admin()) {
			add_action(
				'admin_init',
				array(&$this, 'enqueue_resources')
			);
		}

		// Add a shortcode for displaying galleries
		add_shortcode('ngg_images', array(&$this, 'display_images'));
	}


	/**
	 * Adds the display settings page to wp-admin
	 */
	function add_display_settings_page()
	{
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Display Settings'),
			_('Display Settings'),
			'NextGEN Manage gallery',
			$this->page_name,
			array(&$this->controller, 'index')
		);
	}


	/**
	 * Enqueues static resources for the Display Settings Page
	 */
	function enqueue_resources()
	{
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == $this->page_name) {
			wp_enqueue_script(
				'nextgen_display_settings_page',
				PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js/nextgen_display_settings_page.js',
				array('jquery-ui-accordion'),
				$this->module_version
			);

			// There are many jQuery UI themes available via Google's CDN:
			// See: http://stackoverflow.com/questions/820412/downloading-jquery-css-from-googles-cdn
			wp_enqueue_style(
				'jquery-ui-south-street',
				(is_ssl()?'https':'http').'://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/south-street/jquery-ui.css',
				array(),
				'1.7.0'
			);

			wp_enqueue_style(
				'nextgen_display_settings_page',
				PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/css/nextgen_display_settings_page.css'
			);
		}
	}
}

new M_Gallery_Display();