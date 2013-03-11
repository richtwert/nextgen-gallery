<?php

/***
	{
		Module: photocrati-nextgen_gallery_display,
		Depends: { photocrati-lazy_resources, photocrati-simple_html_dom }
	}
***/

define('NEXTGEN_DISPLAY_SETTINGS_SLUG', 'ngg_display_settings');

class M_Gallery_Display extends C_Base_Module
{
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
	}

	function initialize()
	{
		parent::initialize();

		$this->add_display_types_as_alternative_views();
	}

	/**
	 * Registers each display type as an alternative view
	 */
	function add_display_types_as_alternative_views()
	{
		$view_manager = $this->get_registry()->get_utility('I_Alternative_View_Manager');
		$mapper		  = $this->get_registry()->get_utility('I_Display_Type_Mapper');
		foreach ($mapper->find_all() as $display_type) {
			$view_manager->add(
				'display_type', $display_type->name, $display_type->title
			);
		}
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
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

		$this->get_registry()->add_utility(
			'I_Alternative_View_Manager',
			'C_Alternative_View_Manager'
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

		// Provides fields related to alternative views
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_Alternative_View_Form'
		);

		// Plugin activation routine
		$this->get_registry()->add_adapter(
			'I_Installer',
			'A_Gallery_Display_Installer'
		);

		$this->get_registry()->add_adapter(
			'I_Page_Manager',
			'A_Display_Settings_Page'
		);
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_galleries_js'));

		// Add a shortcode for displaying galleries
		add_shortcode('ngg_images', array(&$this, 'display_images'));
	}

	// TODO: Is this the right place for this?
    function enqueue_galleries_js()
    {
		$router = $this->get_registry()->get_utility('I_Router');
        wp_register_script('nextgen_galleries', $router->get_static_url('nextgen_galleries.js'));
        wp_enqueue_script('nextgen_galleries');
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
			NEXTGEN_DISPLAY_SETTINGS_SLUG,
			array(&$this->controller, 'index_action')
		);
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
