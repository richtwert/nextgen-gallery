<?php

/***
	{
		Module: photocrati-gallery_display,
		Depends: { photocrati-lazy_resources }
	}
***/

class M_Gallery_Display extends C_Base_Module
{
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
			'ngg_display_settings',
			array(&$this->controller, 'index')
		);
	}


	/**
	 * Enqueues static resources for the Display Settings Page
	 */
	function enqueue_resources()
	{
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
	}


	/**
	 * Displays a "displayed gallery" instance
	 */
	function display_images($params, $inner_content=NULL)
	{
		// TODO: This function needs to be moved to a mixin, so that
		// it can be adapted
		$displayed_gallery = NULL;

		// Configure the arguments
		$defaults = array(
			'id'				=>	NULL,
			'source'			=>	NULL,
			'container_ids'		=>	array(),
			'gallery_ids'		=>	array(),
			'album_ids'			=>	array(),
			'tag_ids'			=>	array(),
			'display_type'		=>	NULL,
			'exclusions'		=>	array()
		);
		$args = shortcode_atts($defaults, $params);

		// Are we loading a specific displayed gallery that's persisted?
		$mapper = $this->_get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		if (!is_null($args['id'])) {
			$displayed_gallery = $mapper->find($args['id']);
			unset($mapper); // no longer needed
		}

		// We're generating a new displayed gallery
		else {

			// Perform some conversions...

			// Galleries?
			if ($args['gallery_ids']) {
				$args['source']					= 'galleries';
				$args['container_ids']		= $args['gallery_ids'];
				unset($args['gallery_ids']);
			}

			// Albums ?
			elseif ($args['album_ids']) {
				$args['source']					= 'albums';
				$args['container_ids']		= $args['album_ids'];
				unset($args['albums_ids']);
			}

			// Tags ?
			elseif ($args['tag_ids']) {
				$args['source']					= 'tags';
				$args['container_ids']		= $args['tag_ids'];
				unset($args['tag_ids']);
			}

			// Convert strings to arrays
			if (!is_array($args['container_ids'])) {
				$args['container_ids']	= preg_split("/,|\|/", $args['container_ids']);
			}
			if (!is_array($args['exclusions'])) {
				$args['exclusions']		= preg_split("/,|\|/", $args['exclusions']);
			}

			// Get the display settings
			foreach (array_keys($defaults) as $key) unset($params[$key]);
			$args['display_settings']	= $params;

			// Validate the displayed gallery
			$factory = $this->_get_registry()->get_utility('I_Component_Factory');
			$displayed_gallery = $factory->create('displayed_gallery', $mapper, $args);
			unset($factory);
		}

		// Validate the displayed gallery
		if ($displayed_gallery && $displayed_gallery->validate()) {

			// Set a temporary id
			$displayed_gallery->id(uniqid('temp'));

			// Display!
			$controller = $this->_get_registry()->get_utility(
				'I_Display_Type_Controller', $displayed_gallery->display_type
			);
			$controller->enqueue_resources($displayed_gallery);
			$controller->index($displayed_gallery);
		}
		else return "Invalid Displayed Gallery".print_r($displayed_gallery->get_errors());
	}
}

new M_Gallery_Display();