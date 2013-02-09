<?php

class A_NextGen_Basic_Slideshow_Controller extends Mixin
{
    public $_settings = array();

	/**
	 * Adds framework support for thumbnails
	 */
	function initialize()
	{
        $this->add_mixin('Mixin_NextGen_Basic_Slideshow_Settings');
	}

	/**
	 * Displays the ngglegacy thumbnail gallery.
	 * This method deprecated use of the nggShowGallery() function.
	 * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
	 */
	function index_action($displayed_gallery, $return=FALSE)
	{
		// Get the images to be displayed
        $current_page = (int)$this->param('page', 1);

		if (($images = $displayed_gallery->get_included_entities()))
        {
			// Get the gallery storage component
			$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');

			// Create parameter list for the view
			$params = $displayed_gallery->display_settings;
			$params['storage']				= &$storage;
			$params['images']				= &$images;
			$params['displayed_gallery_id'] = $displayed_gallery->id();
			$params['current_page']			= $current_page;
			$params['effect_code']			= $this->object->get_effect_code($displayed_gallery);
			$params['anchor']				= 'ngg-slideshow-' . $displayed_gallery->id() . '-' . rand(1, getrandmax()) . $current_page;
			$gallery_width					= $displayed_gallery->display_settings['gallery_width'];
			$gallery_height					= $displayed_gallery->display_settings['gallery_height'];
			$params['aspect_ratio']			= $gallery_width/$gallery_height;
			$params['flash_path']			= $this->object->static_url('imagerotator.swf');

			// Are we displayed a flash slideshow?
			if ($displayed_gallery->display_settings['flash_enabled'])
            {
				include_once(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array('lib', 'swfobject.php'))));
                $transient_id = $displayed_gallery->to_transient();
				$mediarss_link = real_site_url('/mediarss?template=playlist_feed&source=displayed_gallery&transient_id=' . $transient_id);
				$params['mediarss_link'] = $mediarss_link;
			}

			$retval = $this->object->render_partial('nextgen_basic_slideshow', $params, $return);
		}

		// No images found
		else {
			$retval = $this->object->render_partial('no_images_found', array(), $return);
		}

		return $retval;
	}

	/**
	 * Enqueues all static resources required by this display type
	 * @param C_Displayed_Gallery $displayed_gallery
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		wp_enqueue_style('nextgen_basic_slideshow_style', $this->static_url('nextgen_basic_slideshow.css'));
        wp_enqueue_script('waitforimages', $this->static_url('jquery.waitforimages.js'));
		$this->call_parent('enqueue_frontend_resources', $displayed_gallery);
	}

    function enqueue_backend_resources()
    {
        wp_enqueue_script('nextgen_basic_slideshow_settings-js', $this->static_url('nextgen_basic_slideshow_settings.js'));
    }

	/**
	 * Provides the url of the JavaScript library required for
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_lib_url()
	{
		return NEXTGEN_GALLERY_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow.js';
	}

	/**
	 * Provides the url of the JavaScript resource used to initialize
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_init_url()
	{
		return NEXTGEN_GALLERY_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow_init.js';
	}
}