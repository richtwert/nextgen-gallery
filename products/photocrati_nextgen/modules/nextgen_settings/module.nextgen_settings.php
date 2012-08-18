<?php

/***
{
	Module:	photocrati-nextgen_settings
}
***/

class M_NextGen_Settings extends C_Base_Module
{
	var $activator = NULL;
	var $page_name = 'ngg_other_options';

	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen_settings',
			'NextGEN Gallery Settings',
			'Provides central management for NextGEN Gallery settings',
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
		$this->activator  = $this->get_registry()->get_utility('I_NextGen_Activator');
		$this->controller = $this->get_registry()->get_utility('I_NextGen_Settings_Controller');
	}


	/**
	 * Register utilities necessary for this module (and the plugin)
	 */
	function _register_utilities()
	{
		/**
		 * Provides a component to manage NextGen Settings
		 */
		$this->get_registry()->add_utility(
			'I_NextGen_Settings',
			'C_NextGen_Settings'
		);

		/**
		 * Provides a component to provide plugin activation
		 */
		$this->get_registry()->add_utility(
			'I_NextGen_Activator',
			'C_NextGen_Activator'
		);

		/**
		 * Provides a utility to perform CRUD operations for Lightbox libraries
		 */
		$this->get_registry()->add_utility(
			'I_Lightbox_Library_Mapper',
			'C_Lightbox_Library_Mapper'
		);

		// Provides the Options page
		$this->get_registry()->add_utility(
			'I_NextGen_Settings_Controller',
			'C_NextGen_Settings_Controller'
		);
	}

	/**
	 * Registers adapters required by this module
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Component_Factory',
			'A_NextGen_Settings_Factory'
		);

		$this->get_registry()->add_adapter(
			'I_MVC_Controller',
			'A_MVC_Validation'
		);

		$this->get_registry()->add_adapter(
			'I_Ajax_Controller',
			'A_Stylesheet_Ajax_Actions'
		);
	}

	/**
	 * Hooks into the WordPress Framework
	 */
	function _register_hooks()
	{
		// Use the NextGEN Activator to run activation routines
		add_action(
			'activate_'.PHOTOCRATI_GALLERY_PLUGIN_BASENAME,
			array(&$this->activator, 'install')
		);

		// Provides menu options for managing NextGEN Settings
		add_action(
			'admin_menu',
			array(&$this, 'add_menu_pages'),
			999
		);

		// Enqueues static resources required
		if (is_admin()) {
			add_action(
				'admin_init',
				array(&$this, 'enqueue_resources')
			);
		}
	}

	/**
	 * Adds menu pages to manage NextGen Settings
	 * @uses action: admin_menu
	 */
	function add_menu_pages()
	{
		// Add the "Options" page
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Gallery - Global Options'),
			_('Global Options'),
			'NextGEN Change options',
			$this->page_name,
			array(&$this->controller, 'index')
		);
	}

	/**
	 * Enqueues static resources required for the Settings page
	 */
	function enqueue_resources()
	{
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == $this->page_name) {
			wp_enqueue_script('farbtastic');
			wp_enqueue_style('farbtastic');

			wp_enqueue_script(
				'nextgen_settings_page',
				PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js/nextgen_settings_page.js',
				array('jquery-ui-accordion'),
				$this->module_version
			);

			// There are many jQuery UI themes available via Google's CDN:
			// See: http://stackoverflow.com/questions/820412/downloading-jquery-css-from-googles-cdn
			wp_enqueue_style(
				PHOTOCRATI_GALLERY_JQUERY_UI_THEME,
				is_ssl() ?
					 str_replace('http:', 'https:', PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL) :
					 PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL,
				array(),
				PHOTOCRATI_GALLERY_JQUERY_UI_THEME_VERSION
			);

			wp_enqueue_style(
				'nextgen_settings_page',
				PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/css/nextgen_settings_page.css',
				array(),
				$this->module_version
			);
		}
	}
}

new M_NextGen_Settings();