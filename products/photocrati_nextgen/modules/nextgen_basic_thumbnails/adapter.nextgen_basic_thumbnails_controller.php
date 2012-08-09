<?php

class A_NextGen_Basic_Thumbnails_Controller extends Mixin
{
	var $_max_images_to_fetch = 20;


	/**
	 * Displays the ngglegacy thumbnail gallery.
	 * This method deprecated use of the nggShowGallery() function.
	 * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
	 */
	function index($displayed_gallery)
	{
		// Get the images to be displayed
		$images = $displayed_gallery->get_images($this->_max_images_to_fetch);

		// Are there images to display?
		if ($images) {

			// $_GET from wp_query
			$show    = get_query_var('show');
			$pid     = get_query_var('pid');
			$pageid  = get_query_var('pageid');

			switch(get_query_var('show')) {

				// We're not displaying a slideshow
				case 'slide':

					// Get settings object to determine default dimensions
					// of the image rotator
					$settings = $this->object->_get_registry()->get_utility(
						'I_NextGen_Settings'
					);

					// Render the basic slideshow
					// This is ugly - but it's a first attempt to convert
					// the ngglegacy galleries into something that makes
					// sense.
					global $nggRewrite;
					$args['show'] = "gallery";
					$slideshow_link_text = $displayed_gallery->display_settings['slideshow_link_text'];
					echo '<div class="ngg-galleryoverview">';
					echo '<div class="slideshowlink"><a class="slideshowlink" href="' . $nggRewrite->get_permalink($args) . '">'.nggGallery::i18n($slideshow_link_text).'</a></div>';
					$displayed_gallery->display_type = 'photocrati-nextgen_basic_slideshow';
					$controller = $this->_get_registry()->get_utility(
						'I_Display_Type_Controller',
						$displayed_gallery->display_type
					);
					$controller->enqueue_resources($displayed_gallery);
					$controller->index($displayed_gallery);
					echo  '</div>'."\n";
					echo '<div class="ngg-clear"></div>'."\n";
					break;

				// We'l continue to display the thumbnail gallery
				default:
					echo nggCreateGallery(
						$images,
						$displayed_gallery->id(),
						$displayed_gallery->display_settings['template'],
						$displayed_gallery->display_settings['images_per_page']);
			}
		}
		else {
			$this->render_partial("no_images_found");
		}
	}


	/**
     * Convert the displayed gallery settings to the names of legacy settings
     * @param array $settings
     * @return array
     */
    function _displayed_gallery_settings_to_legacy_settings($settings)
    {
        return array(
            'galShowSlide'      =>  $settings['show_slideshow_link'],
            'galTextSlide'      =>  $settings['slideshow_link_text'],
            'galColumns'        =>  $settings['num_of_columns'],
            'usePicLens'        =>  $settings['show_piclens_link'],
            'galImages'         =>  $settings['images_per_page'],
            'galTextGallery'    =>  $settings['thumbnail_link_text'],
            'piclens_link_text' =>  $settings['piclens_link_text']

        );
    }


	/**
	 * Provides the url of the JavaScript library required for
	 * NextGEN Basic Thumbnails to display
	 * @return string
	 */
	function _get_js_lib_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS_JS_URL.'/nextgen_basic_thumbnails.js';
	}

	/**
	 * Provides the url of the JavaScript resource used to initialize
	 * NextGEN Basic Thumbnails to display
	 * @return string
	 */
	function _get_js_init_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS_JS_URL.'/nextgen_basic_thumbnails_init.js';
	}
}