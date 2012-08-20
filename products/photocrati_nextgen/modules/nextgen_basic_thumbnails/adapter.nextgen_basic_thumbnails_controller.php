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
				$pagination = $pagination->create_navigation($current_page, $total, $images_per_page);
			}

			// Determine what the slideshow link would be. TODO: Figure this out
			$slideshow_link = 'http://www.google.ca';

			// Determine what the piclens link would be
			if ($displayed_gallery->display_settings['show_piclens_link']) {
				$params = json_encode($displayed_gallery->get_entity());
				$mediarss_link = real_site_url('/mediarss?source=displayed_gallery&params='.$params);
				$piclens_link = "javascript:PicLensLite.start({feedUrl:'{$mediarss_link}'});";
			}

			// Get the gallery storage component
			$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');

            // The render functions require different processing
            if (!empty($displayed_gallery->display_settings['template']))
            {
                $params = $this->object->_prepare_legacy_render(
                    $images,
                    $displayed_gallery,
                    $slideshow_link,
                    $piclens_link,
                    $pagination
                );
                $this->object->legacy_render($displayed_gallery->display_settings['template'], $params, False);
            }
            else {
                $params = $displayed_gallery->display_settings;
                $params['storage']				= &$storage;
                $params['images']				= &$images;
                $params['displayed_gallery_id'] = $displayed_gallery->id();
                $params['current_page']			= $current_page;
                $params['slideshow_link']		= $slideshow_link;
                $params['piclens_link']			= $piclens_link;
                $params['effect_code']			= $this->object->get_effect_code($displayed_gallery);
                $params['pagination']			= $pagination;
                $this->object->render_partial('nextgen_basic_thumbnails', $params);
            }
		}
		else {
			$this->object->render_partial("no_images_found");
		}
	}

    /**
     * Prepares the objects necessary for legacy template rendering.
     *
     * This function is separated for code clarity.
     * @param array $images Array of image objects
     * @param string $slideshow_link Slideshow HTML string
     * @param string string $piclens_link Piclens HTML string
     * @param string $pagination Pagination HTML string
     * @return array
     */
    function _prepare_legacy_render($images, $displayed_gallery, $slideshow_link, $piclens_link, $pagination)
    {
        $pid = get_query_var('pid');
        if (!is_numeric($pid) && !empty($pid))
        {
            $picture = $this->object
                            ->get_registry()
                            ->get_utility('I_Gallery_Image_Mapper')
                            ->find_first(array('image_slug = %s', '2176305717_7e602fbfbe_o'));
            $id_field = $picture->id_field;
            $pid = $picture->$id_field;
        }

        $picture_list = array();
        $current_pid = null;
        foreach ($images as $image) {
            $new_image = new C_NextGen_Gallery_Image_Wrapper($image);
            if ($pid == $new_image->id)
            {
                $current_pid = $new_image;
            }
            $picture_list[] = $new_image;
        }
        reset($picture_list);
        $current_pid = (is_null($current_pid)) ? current($picture_list) : $current_pid;
        $current_page = (get_the_ID() == FALSE) ? 0 : get_the_ID();

        $gallery_map = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
        $orig_gallery = $gallery_map->find(current($picture_list)->galleryid);
        $id_field = $orig_gallery->id_field;

        $gallery = new stdclass;
        $gallery->ID = $orig_gallery->$id_field;
        $gallery->show_slideshow = false;
        $gallery->show_piclens = false;
        $gallery->name = stripslashes($orig_gallery->name);
        $gallery->title = stripslashes($orig_gallery->title);
        $gallery->description = html_entity_decode(stripslashes($orig_gallery->galdesc));
        $gallery->pageid = $orig_gallery->pageid;
        $gallery->anchor = 'ngg-gallery-' . $orig_gallery->$id_field . '-' . $current_page;
        $gallery->displayed_gallery = &$displayed_gallery;
        $gallery->columns = intval($displayed_gallery->display_settings['number_of_columns']);
        $gallery->imagewidth = ($gallery->columns > 0) ? 'style="width:' . floor(100/$gallery->columns) . '%;"' : '';

        if (is_integer($gallery->ID)) {
            if ($displayed_gallery->display_settings['show_slideshow_link']) {
                $gallery->show_slideshow = TRUE;
                $gallery->slideshow_link = $slideshow_link; // $nggRewrite->get_permalink(array ( 'show' => 'slide') );
                $gallery->slideshow_link_text = $displayed_gallery->display_settings['slideshow_text_link'];
            }

            if ($displayed_gallery->display_settings['show_piclens_link']) {
                $gallery->show_piclens = true;
                $gallery->piclens_link = $piclens_link;
                $gallery->piclens_link_text = $displayed_gallery->display_settings['piclens_text_link'];
            }
        }
        $gallery = apply_filters('ngg_gallery_object', $gallery, 4);

        return array(
            'pagination' => $pagination,
            'gallery' => $gallery,
            'images' => $picture_list,
            'current' => $current_pid,
            'next' => FALSE,
            'prev' => FALSE
        );
    }

	/**
	 * Enqueues all static resources required by this display type
	 * @param C_Displayed_Gallery $displayed_gallery
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		if ($displayed_gallery->display_settings['show_piclens_link']) {
			wp_enqueue_script(
				'piclens',
				(is_ssl()?'https':'http').'://lite.piclens.com/current/piclens_optimized.js'
			);
		}

		$this->call_parent('enqueue_frontend_resources', $displayed_gallery);
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

    /**
     * Renders the images_per_page settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_images_per_page_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_images_per_page', array(
            'display_type_name' => $display_type->name,
            'images_per_page_label' => _('Images per page:'),
            'images_per_page' => $display_type->settings['images_per_page'],
        ), True);
    }

    /**
     * Renders the number_of_columns settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_number_of_columns_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_number_of_columns', array(
            'display_type_name' => $display_type->name,
            'number_of_columns_label' => _('Number of columns to display:'),
            'number_of_columns' => $display_type->settings['number_of_columns']
        ), True);
    }

    /**
     * Renders the slideshow_link_text settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_slideshow_link_text_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_slideshow_link_text', array(
            'display_type_name' => $display_type->name,
            'slideshow_link_text_label' => _('Slideshow text link:'),
            'slideshow_link_text' => $display_type->settings['slideshow_link_text'],
        ), True);
    }

    /**
     * Renders the piclens_link_text settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_piclens_link_text_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_piclens_link_text', array(
            'display_type_name' => $display_type->name,
            'piclens_link_text_label' => _('Piclens text link:'),
            'piclens_link_text' => $display_type->settings['piclens_link_text']
        ), True);
    }

    /**
     * Renders the show_slideshow_link settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_show_slideshow_link_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_show_slideshow_link', array(
            'display_type_name' => $display_type->name,
            'show_slideshow_link_label' => _('Show slideshow link:'),
            'show_slideshow_link' => $display_type->settings['show_slideshow_link']
        ), True);
    }

    /**
     * Renders the show_piclens_link settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_show_piclens_link_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_show_piclens_link', array(
            'display_type_name' => $display_type->name,
            'show_piclens_link_label' => _('Show piclens link:'),
            'show_piclens_link' => $display_type->settings['show_piclens_link']
        ), True);
    }


    /**
	 * Returns a list of fields to render on the settings page
	 */
	function _get_field_names()
	{
		return array(
			'thumbnail_dimensions',
            'nextgen_basic_thumbnails_images_per_page',
            'nextgen_basic_thumbnails_number_of_columns',
            'nextgen_basic_thumbnails_slideshow_link_text',
            'nextgen_basic_thumbnails_piclens_link_text',
            'nextgen_basic_thumbnails_show_slideshow_link',
            'nextgen_basic_thumbnails_show_piclens_link',
            'nextgen_basic_templates_template'
		);
	}
}
