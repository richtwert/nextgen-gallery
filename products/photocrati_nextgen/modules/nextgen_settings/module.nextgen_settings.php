<?php

/***
{
	Module:	photocrati-nextgen_settings
}
***/

class M_NextGen_Settings extends C_Base_Module
{
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
		$this->controller  = $this->get_registry()->get_utility('I_NextGen_Settings_Controller');
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
         * Provides a counterpart deactivation routine
         */
        $this->get_registry()->add_utility(
            'I_NextGen_Deactivator',
            'C_NextGen_Deactivator'
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

        // Provides the deactivator "check uninstall" page
        $this->get_registry()->add_utility(
            'I_NextGen_Deactivator_Controller',
            'C_NextGen_Deactivator_Controller'
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

        // plugin deactivation routine
        $this->get_registry()->add_adapter('I_NextGen_Deactivator', 'A_NextGen_Settings_Deactivation');

        // adds some AJAX-support routes like updating watermark previews
        $this->get_registry()->add_adapter('I_Router', 'A_NextGen_Settings_Routes');
	}

	/**
	 * Hooks into the WordPress Framework
	 */
	function _register_hooks()
	{
		register_activation_hook(NEXTGEN_GALLERY_PLUGIN_BASENAME, get_class().'::activate');
		register_uninstall_hook(NEXTGEN_GALLERY_PLUGIN_BASENAME, get_class().'::deactivate');

		// Provides menu options for managing NextGEN Settings
		add_action(
			'admin_menu',
			array(&$this, 'add_menu_pages'),
			999
		);
	}

	static function activate()
	{
		C_Component_Registry::get_instance()->get_utility('I_NextGen_Activator')->install();
	}

	static function deactivate()
	{
		C_Component_Registry::get_instance()->get_utility('I_NextGen_Deactivator')->deactivate();
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
			_('NextGEN Gallery - Other Options'),
			_('Other Options'),
			'NextGEN Change options',
			$this->page_name,
			array(&$this->controller, 'index_action')
		);

        // nextgen-deactivator 'check uninstall' page
        add_submenu_page(
            NULL,
            _('NextGEN Gallery - Check Uninstall'),
            _('Check Uninstall'),
            'administrator',
            'ngg_deactivator_check_uninstall',
            array(
                $this->get_registry()->get_utility('I_NextGen_Deactivator_Controller'),
                'index_action'
            )
        );
	}
}

new M_NextGen_Settings();
