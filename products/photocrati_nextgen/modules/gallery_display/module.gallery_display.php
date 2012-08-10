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
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
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


	function _register_hooks()
	{
		add_shortcode('ngg_images', array(&$this, 'display_images'));
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