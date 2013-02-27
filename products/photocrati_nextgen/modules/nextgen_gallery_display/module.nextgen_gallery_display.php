<?php

/***
	{
		Module: photocrati-nextgen_gallery_display,
		Depends: { photocrati-lazy_resources, photocrati-simple_html_dom }
	}
***/

class M_Gallery_Display extends C_Base_Module
{
	var $display_settings_page_name     = 'ngg_display_settings';
	var $controller                     = NULL;

	function define()
	{
		parent::define(
			'photocrati-nextgen_gallery_display',
			'Gallery Display',
			'Provides the ability to display gallery of images',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);

		$this->add_mixin('Mixin_MVC_Controller_Rendering');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->controller   = $this->get_registry()->get_utility('I_Display_Settings_Controller');
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
		// This utility provides a controller that renders the
		// Display Settings page, used to control global values for
		// all display types
		$this->get_registry()->add_utility(
			'I_Display_Settings_Controller',
			'C_Display_Settings_Controller'
		);

		// This utility provides a controller to render the settings form
		// for a display type, or render the front-end of a display type
		$this->get_registry()->add_utility(
			'I_Display_Type_Controller',
			'C_Display_Type_Controller'
		);

		// This utility provides a datamapper for Display Types
		$this->get_registry()->add_utility(
			'I_Display_Type_Mapper',
			'C_Display_Type_Mapper'
		);

		// This utility provides a datamapper for Displayed Galleries. A
		// displayed gallery is the association between some entities (images
		//or galleries) and a display type
		$this->get_registry()->add_utility(
			'I_Displayed_Gallery_Mapper',
			'C_Displayed_Gallery_Mapper'
		);

		// This utility provides a datamapper for Displayed Gallery Sources. A
		// source instructs a displayed gallery where the entities are to be
		// fetched from - e.g. galleries, albums, etc.
		$this->get_registry()->add_utility(
			'I_Displayed_Gallery_Source_Mapper',
			'C_Displayed_Gallery_Source_Mapper'
		);

        // This utility provides the capabilities of rendering a display type
        $this->get_registry()->add_utility(
            'I_Displayed_Gallery_Renderer',
            'C_Displayed_Gallery_Renderer'
        );
	}


	/**
	 * Registers adapters required for this module
	 */
	function _register_adapters()
	{
		// Provides factory methods for creating display type and
		// displayed gallery instances
		$this->get_registry()->add_adapter(
			'I_Component_Factory', 'A_Gallery_Display_Factory'
		);

		// Enqueues resources required for the Display Settings page
		$this->get_registry()->add_adapter(
			'I_NextGen_Backend_Controller',
			'A_Display_Settings_Page_Resources'
		);

        // plugin deactivation routine
        $this->get_registry()->add_adapter('I_NextGen_Deactivator', 'A_Gallery_Display_Deactivation');

		// Plugin activation routine
		$this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Gallery_Display_Activation');
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
		// Add the display settings page to wp-admin
		add_action('admin_menu', array(&$this, 'add_display_settings_page'), 900);

		// Enqueues static resources required
		if (is_admin()) {
			add_action(
				'admin_enqueue_scripts',
				array(&$this, 'enqueue_resources'),
				1
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
			_('NextGEN Gallery & Album Settings'),
			_('Gallery Settings'),
			'NextGEN Manage gallery',
			$this->display_settings_page_name,
			array(&$this->controller, 'index_action')
		);
	}


	/**
	 * Enqueues static resources for the Display Settings Page
	 */
	function enqueue_resources()
	{
        // for tooltip styling
        if (isset($_GET['page']) && $_GET['page'] == 'nggallery-manage-gallery')
        {
            wp_enqueue_style('nextgen_display_settings_page', $this->static_url('nextgen_display_settings_page.css'));
        }
	}


	/**
	 * Provides the [display_images] shortcode
	 * @param array $params
	 * @param string $inner_content
	 * @return string
	 */
	function display_images($params, $inner_content=NULL)
	{
		$renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
		return $renderer->display_images($params, $inner_content);
	}

    /**
     * Gets a value from the parameter array, and if not available, uses the default value
     * @param string $name
     * @param mixed $default
     * @param array $params
     * @return mixed
     */
    function _get_param($name, $default, $params)
    {
        return (isset($params[$name])) ? $params[$name] : $default;
    }
}

new M_Gallery_Display();
