<?php

/***
{
	Module:	photocrati-nextgen_admin
}
***/

define('NEXTGEN_FS_ACCESS_SLUG', 'ngg_fs_access');

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
			'Provides a framework for adding Administration pages',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function initialize()
	{
		parent::initialize();
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

		$this->get_registry()->add_utility(
			'I_Page_Manager',
			'C_Page_Manager'
		);

		// Provides a form manager
		$this->get_registry()->add_utility(
			'I_Form_Manager',
			'C_Form_Manager'
		);

		// Provides a form
		$this->get_registry()->add_utility(
			'I_Form',
			'C_Form'
		);
	}

	/**
	 * Registers adapters required by this module
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Settings_Manager',
			'A_NextGen_Admin_Settings'
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
			'I_Router',
			'A_NextGen_Settings_Routes'
		);

		$this->get_registry()->add_adapter(
			'I_Installer',
			'A_NextGen_Admin_Installer'
		);

		$this->get_registry()->add_adapter(
			'I_Page_Manager',
			'A_NextGen_Admin_Pages'
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
		$this->get_registry()->get_utility('I_Page_Manager')->setup();
	}
}

new M_NextGen_Admin();
