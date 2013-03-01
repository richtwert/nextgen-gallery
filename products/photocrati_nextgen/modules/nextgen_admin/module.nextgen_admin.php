<?php

/***
{
	Module:	photocrati-nextgen_admin
}
***/

class M_NextGen_Admin extends C_Base_Module
{
	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen_admin',
			'NextGEN Administration',
			'Provides central management for NextGEN Gallery',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}


	/**
	 * Register utilities necessary for this module (and the plugin)
	 */
	function _register_utilities()
	{
		// Provides a NextGEN Administation page
		$this->get_registry()->add_utility(
			'I_NextGen_Admin_Page',
			'C_NextGen_Admin_Page_Controller'
		);
	}

	/**
	 * Registers adapters required by this module
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Settings_Manager',
			'A_NextGen_Settings_Manager'
		);

		$this->get_registry()->add_adapter(
			'I_MVC_Controller',
			'A_MVC_Validation'
		);

		$this->get_registry()->add_adapter(
			'I_Ajax_Controller',
			'A_Stylesheet_Ajax_Actions'
		);

		$this->get_registry()->add_adapter(
			'I_NextGen_Admin_Page',
			'A_Display_Settings_Controller',
			'display_settings'
		);

        // adds some AJAX-support routes like updating watermark previews
        $this->get_registry()->add_adapter(
			'I_Router',
			'A_NextGen_Settings_Routes'
		);
	}

	/**
	 * Hooks into the WordPress Framework
	 */
	function _register_hooks()
	{
		// Provides menu options for managing NextGEN Settings
		add_action('admin_menu', array(&$this, 'add_menu_pages'), 999);
	}

	/**
	 * Adds menu pages to manage NextGen Settings
	 * @uses action: admin_menu
	 */
	function add_menu_pages()
	{
		// Get controllers for pages
		$display_settings_controller = $this->get_registry()->get_utility(
			'I_NextGen_Admin_Page', 'display_settings'
		);
		$other_options_controller	 = $this->get_registry()->get_utility(
			'I_NextGen_Admin_Page', 'other_options'
		);
		$uninstall_controller		 = $this->get_registry()->get_utility(
			'I_NextGen_Admin_Page',	'uninstall'
		);

		// Register menus
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Gallery & Album Settings'),
			_('Gallery Settings'),
			'NextGEN Manage gallery',
			'ngg_display_settings',
			array(&$display_settings_controller, 'index_action')
		);

		// Add the "Options" page
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Gallery - Other Options'),
			_('Other Options'),
			'NextGEN Change options',
			'ngg_other_options',
			array(&$other_options_controller, 'index_action')
		);

        // Add Uninstall Page
        add_submenu_page(
            NULL,
            _('NextGEN Gallery - Check Uninstall'),
            _('Check Uninstall'),
            'administrator',
            'ngg_uninstall',
            array(&$uninstall_controller, 'index_action')
        );
	}
}

new M_NextGen_Admin();
