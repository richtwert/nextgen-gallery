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
			'A_NextGen_Admin_Settings', $this->module_id
		);

		$this->get_registry()->add_adapter(
			'I_MVC_Controller',
			'A_MVC_Validation'
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
			'A_NextGen_Admin_Default_Pages'
		);
	}

	/**
	 * Hooks into the WordPress Framework
	 */
	function _register_hooks()
	{
        // Register scripts
        add_action('init', array(&$this, 'register_scripts'));

		// Provides menu options for managing NextGEN Settings
		add_action('admin_menu', array(&$this, 'add_menu_pages'), 999);
	}


    function register_scripts()
    {
        $router = $this->get_registry()->get_utility('I_Router');
        wp_register_script('gritter', $router->get_static_url('nextgen_admin#gritter/gritter.min.js'), array('jquery'));
        wp_register_style('gritter',  $router->get_static_url('nextgen_admin#gritter/css/gritter.css'));
        wp_register_script('ngg_progressbar', $router->get_static_url('nextgen_admin#ngg_progressbar.js'), array('gritter'));
        wp_register_style('ngg_progressbar', $router->get_static_url('nextgen_admin#ngg_progressbar.css'), array('gritter'));
        $match = preg_quote("/wp-admin/post.php", "#");
        if (preg_match("#{$match}#", $_SERVER['REQUEST_URI'])) {
            wp_enqueue_script('ngg_progressbar');
            wp_enqueue_style('ngg_progressbar');
        }
    }

	/**
	 * Adds menu pages to manage NextGen Settings
	 * @uses action: admin_menu
	 */
	function add_menu_pages()
	{
		$this->get_registry()->get_utility('I_Page_Manager')->setup();
	}

    function set_file_list()
    {
        return array(
            'adapter.fs_access_page.php',
            'adapter.mvc_validation.php',
            'adapter.nextgen_admin_installer.php',
            'adapter.nextgen_admin_default_pages.php',
            'adapter.nextgen_admin_settings.php',
            'adapter.nextgen_settings_routes.php',
            'class.form.php',
            'class.form_manager.php',
            'class.nextgen_admin_page_controller.php',
            'class.page_manager.php',
            'interface.form.php',
            'interface.form_manager.php',
            'interface.nextgen_admin_page.php',
            'interface.nextgen_settings.php',
            'interface.page_manager.php'
        );
    }
}

new M_NextGen_Admin();
