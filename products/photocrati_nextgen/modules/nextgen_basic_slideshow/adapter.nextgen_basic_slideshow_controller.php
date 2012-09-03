<?php

class A_NextGen_Basic_Slideshow_Controller extends Mixin
{
	/**
	 * Adds framework support for thumbnails
	 */
	function initialize()
	{
		$this->add_mixin('Mixin_Thumbnail_Display_Type_Controller');
	}

	/**
	 * Displays the ngglegacy thumbnail gallery.
	 * This method deprecated use of the nggShowGallery() function.
	 * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
	 */
	function index($displayed_gallery)
	{
		// Get the images to be displayed
		$current_page = get_query_var('nggpage');
		if (!$current_page) $current_page = 1;
		$images_per_page = $displayed_gallery->display_settings['images_per_page'];
		$offset = $images_per_page * ($current_page-1);
		$images = $displayed_gallery->get_images($images_per_page, $offset);
		$total	= $displayed_gallery->get_image_count();
		$pagination = FALSE;

		// Are there images to display?
		if ($images) {

			/***
			// We try to replicate what a call to nggShowGallery() would
			// render as much as possible. The reason why we don't make a call
			// to nggShowGallery() is that it assumes that only one gallery
			// is being displayed, and I don't feel confident modifying it
			// to behave otherwise. I'd sooner replicate the look n' feel
			// and deprecate the nggShowGallery() method
			***/

			// Create pagination
			if ($images_per_page) {
				$pagination = new nggNavigation;
				$pagination = $pagination->create_navigation(
					$current_page, $total, $images_per_page
				);
			}

			// Get the gallery storage component
			$storage = $this->object->get_registry()->get_utility(
				'I_Gallery_Storage'
			);

			$params = $displayed_gallery->display_settings;
			$params['storage']				= &$storage;
			$params['images']				= &$images;
			$params['displayed_gallery_id'] = $displayed_gallery->id();
			$params['current_page']			= $current_page;
			$params['effect_code']			= $this->object->get_effect_code($displayed_gallery);
			$params['pagination']			= $pagination;

			$this->object->render_partial('nextgen_basic_slideshow', $params);

		}
		else {
			$this->object->render_partial("no_images_found");
		}
	}

	/**
	 * Enqueues all static resources required by this display type
	 * @param C_Displayed_Gallery $displayed_gallery
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		$this->call_parent('enqueue_frontend_resources', $displayed_gallery);
	}


	/**
	 * Provides the url of the JavaScript library required for
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_lib_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow.js';
	}

	/**
	 * Provides the url of the JavaScript resource used to initialize
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_init_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow_init.js';
	}

	/**
	 * Renders the images_per_page settings field
	 *
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_nextgen_basic_slideshow_images_per_page_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_images_per_page', array(
					'display_type_name' => $display_type->name,
					'images_per_page_label' => _('Images per page:'),
					'images_per_page' => $display_type->settings['images_per_page'],
			), True);
	}
	
	function _render_nextgen_basic_slideshow_cycle_interval_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_cycle_interval', array(
					'display_type_name' => $display_type->name,
					'cycle_interval_label' => _('Interval:'),
					'cycle_interval' => $display_type->settings['cycle_interval'],
			), True);
	}
	
	function _render_nextgen_basic_slideshow_cycle_effect_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_cycle_effect', array(
					'display_type_name' => $display_type->name,
					'cycle_effect_label' => _('Effect:'),
					'cycle_effect' => $display_type->settings['cycle_effect'],
			), True);
	}

	
	function _render_nextgen_basic_slideshow_gallery_dimensions_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_gallery_dimensions', array(
					'display_type_name' => $display_type->name,
					'gallery_dimensions_label' => _('Gallery Dimensions:'),
					'gallery_width' => $display_type->settings['gallery_width'],
					'gallery_height' => $display_type->settings['gallery_height'],
			), True);
	}

	/**
	 * Returns a list of fields to render on the settings page
	 */
	function _get_field_names()
	{
		return array(
			'thumbnail_dimensions',
			'nextgen_basic_slideshow_gallery_dimensions',
			'nextgen_basic_slideshow_images_per_page',
			'nextgen_basic_slideshow_cycle_interval',
			'nextgen_basic_slideshow_cycle_effect',
		);
	}
}
