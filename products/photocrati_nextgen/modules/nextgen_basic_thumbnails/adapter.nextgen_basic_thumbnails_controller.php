<?php

class A_NextGen_Basic_Thumbnails_Controller extends Mixin
{
	/**
	 * Adds framework support for thumbnails
	 */
	function initialize()
	{
		$this->add_mixin('Mixin_Thumbnail_Display_Type_Controller');
        $this->add_mixin('Mixin_NextGen_Basic_Templates');
        $this->add_mixin('Mixin_Basic_Pagination');
        $this->add_mixin('Mixin_NextGen_Basic_Thumbnails_Settings');
	}

	/**
	 * Displays the ngglegacy thumbnail gallery.
	 * This method deprecated use of the nggShowGallery() function.
	 * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
	 */
	function index_action($displayed_gallery, $return=FALSE)
	{
        $display_settings = $displayed_gallery->display_settings;
        $current_page = (int)$this->param('page', $displayed_gallery->id(), 1);
        $offset = $display_settings['images_per_page'] * ($current_page - 1);
        $storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');
        $total = $displayed_gallery->get_entity_count();
        $gallery_id = $displayed_gallery->id();

        // Get the images to be displayed
        if ($display_settings['images_per_page'] > 0 && $display_settings['show_all_in_lightbox'])
        {
            // the "Add Hidden Images" feature works by loading ALL images and then marking the ones not on this page
            // as hidden (style="display: none")
            $images = $displayed_gallery->get_included_entities();
            $i = 0;
            foreach ($images as &$image) {
                if ($i < $display_settings['images_per_page'] * ($current_page - 1))
                {
                    $image->hidden = TRUE;
                }
                elseif ($i >= $display_settings['images_per_page'] * ($current_page))
                {
                    $image->hidden = TRUE;
                }
                $i++;
            }
        }
        else {
            // just display the images for this page, as normal
            $images = $displayed_gallery->get_included_entities($display_settings['images_per_page'], $offset);
        }

        if (in_array($displayed_gallery->source, array('random', 'recent')))
        {
            $display_settings['disable_pagination'] = TRUE;
        };

		// Are there images to display?
		if ($images) {

			// Create pagination
			if ($display_settings['images_per_page'] && !$display_settings['disable_pagination']) {
                $pagination_result = $this->object->create_pagination(
                    $current_page,
                    $total,
                    $display_settings['images_per_page'],
                    urldecode($this->object->param('ajax_pagination_referrer'))
                );
                $this->object->remove_param('ajax_pagination_referrer');
                $pagination_prev = $pagination_result['prev'];
                $pagination_next = $pagination_result['next'];
                $pagination      = $pagination_result['output'];
			} else {
                list($pagination_prev, $pagination_next, $pagination) = array(NULL, NULL, NULL);
            }

            if ($display_settings['show_piclens_link'] || $display_settings['ajax_pagination'])
                $gallery_id = $displayed_gallery->to_transient();

			$thumbnail_size_name = 'thumbnail';

			if ($display_settings['override_thumbnail_settings'])
            {
                $dynthumbs = $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');

                if ($dynthumbs != null)
                {
                    $dyn_params = array(
                        'width' => $display_settings['thumbnail_width'],
                        'height' => $display_settings['thumbnail_height'],
                    );

                    if ($display_settings['thumbnail_quality'])
                        $dyn_params['quality'] = $display_settings['thumbnail_quality'];

                    if ($display_settings['thumbnail_crop'])
                        $dyn_params['crop'] = true;

                    if ($display_settings['thumbnail_watermark'])
                        $dyn_params['watermark'] = true;

                    $thumbnail_size_name = $dynthumbs->get_size_name($dyn_params);
                }
            }

            // Determine what the piclens link would be
            $piclens_link = '';
            if ($display_settings['show_piclens_link']) {
				$mediarss_link = $this->object->get_router()->get_url('/mediarss?source=displayed_gallery&transient_id=' . $gallery_id);
                $piclens_link = "javascript:PicLensLite.start({feedUrl:'{$mediarss_link}'});";
            }

            // The render functions require different processing
            if (!empty($display_settings['template']))
            {
                $params = $this->object->prepare_legacy_parameters(
                    $images,
                    $displayed_gallery,
                    array(
                        'next' => (empty($pagination_next)) ? FALSE : $pagination_next,
                        'prev' => (empty($pagination_prev)) ? FALSE : $pagination_prev,
                        'pagination' => $pagination,
                        'alternative_view_link_url' => $display_settings['alternative_view_link_url'],
                        'piclens_link' => $piclens_link
                    )
                );
                return $this->object->legacy_render($display_settings['template'], $params, $return, 'gallery');
            }
            else {
                $params = $display_settings;
                $params['storage']				= &$storage;
                $params['images']				= &$images;
                $params['displayed_gallery_id'] = $gallery_id;
                $params['current_page']			= $current_page;
                $params['piclens_link']			= $piclens_link;
                $params['effect_code']			= $this->object->get_effect_code($displayed_gallery);
                $params['pagination']			= $pagination;
                $params['thumbnail_size_name']			= $thumbnail_size_name;
                return $this->object->render_partial('nextgen_basic_thumbnails', $params, $return);
            }
		}
		else {
			return $this->object->render_partial("no_images_found", array(), $return);
		}
	}

	/**
	 * Enqueues all static resources required by this display type
	 * @param C_Displayed_Gallery $displayed_gallery
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
        wp_enqueue_style('nextgen_basic_thumbnails_style', $this->static_url('nextgen_basic_thumbnails.css'));

		if ($displayed_gallery->display_settings['show_piclens_link'])
			wp_enqueue_script('piclens', $this->static_url('piclens/lite/piclens.js'));

        if ($displayed_gallery->display_settings['ajax_pagination'])
            wp_enqueue_script('nextgen-basic-thumbnails-ajax-pagination', $this->object->static_url('ajax_pagination.js'));

        $this->call_parent('enqueue_frontend_resources', $displayed_gallery);
	}

	/**
	 * Provides the url of the JavaScript library required for
	 * NextGEN Basic Thumbnails to display
	 * @return string
	 */
	function _get_js_lib_url()
	{
        return $this->object->static_url('nextgen_basic_thumbnails.js');
	}

	/**
	 * Provides the url of the JavaScript resource used to initialize
	 * NextGEN Basic Thumbnails to display
	 * @return string
	 */
	function _get_js_init_url()
	{
        return $this->object->static_url('nextgen_basic_thumbnails_init.js');
	}
}