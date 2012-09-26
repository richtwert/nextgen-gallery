<?php

class Mixin_NextGen_Basic_Templates extends A_NextGen_Basic_Template_Resources
{
    /**
     * Renders 'template' settings field
     *
     * @param $display_type
     * @return mixed
     */
    function _render_nextgen_basic_templates_template_field($display_type)
    {
        return $this->object->render_partial(
            'nextgen_basic_templates_settings_template',
            array(
                'display_type_name' => $display_type->name,
                'template_label' => _('Template'),
                'template' => $display_type->settings['template'],
            ),
            True
        );
    }

    /**
     * Renders NextGen-Legacy style templates
     *
     * @param string $template_name File name
     * @param array $vars Specially formatted array of parameters
     * @param bool $callback
	 * @param bool $return
     */
    function legacy_render($template_name, $vars = array(), $return=FALSE)
    {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        // hook into the render feature to allow other plugins to include templates
        $custom_template = apply_filters('ngg_render_template', false, $template_name);
        if (($custom_template != false) && file_exists($custom_template))
        {
            include($custom_template);
            return;
        }

        if ('.php' != substr($template_name, -4))
        {
            $template_name = $template_name . '.php';
        }

        foreach ($this->object->get_template_directories() as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $template_name))
            {
				if ($return) {
					ob_start();
					include ($dir . DIRECTORY_SEPARATOR . $template_name);
					$retval = ob_get_contents();
					ob_end_clean();
					return $retval;
				}
				else {
					include ($dir . DIRECTORY_SEPARATOR . $template_name);
					return;
				}
            }
        }

        // test without the "-template" name one time more
        $template_name = array_shift(explode('-', $template_name , 2));
        $this->object->legacy_render($template_name, $vars);
    }

    /**
     * Returns the parameter objects necessary for legacy template rendering (legacy_render())
     *
     * @param array $images Array of image objects
     * @param string $slideshow_link Slideshow HTML string
     * @param string string $piclens_link Piclens HTML string
     * @param string $pagination Pagination HTML string
     * @return array
     */
    function prepare_legacy_parameters($images, $displayed_gallery, $params = array())
    {
        // setup
		$image_map	  = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
		$gallery_map  = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
		$image_key	  = $image_map->get_primary_key_column();
		$gallery_key  = $gallery_map->get_primary_key_column();
        $pid          = get_query_var('pid');

        // because picture_list implements ArrayAccess any array-specific actions must be taken on
        // $picture_list->container or they won't do anything
        $picture_list = new C_Image_Wrapper_Collection();
        $current_pid  = NULL;

        // begin processing
        $current_page = (get_the_ID() == FALSE) ? 0 : get_the_ID();

        // determine what the "current image" is; used mostly for carousel
        if (!is_numeric($pid) && !empty($pid))
        {
            $picture = $image_map->find_first(array('image_slug = %s', $pid));
            $pid = $picture->$image_key;
        }

        // create our new wrappers
        foreach ($images as $image) {
            $new_image = new C_Image_Wrapper($image, $displayed_gallery);
            if ($pid == $new_image->$image_key)
            {
                $current_pid = $new_image;
            }
            $picture_list[] = $new_image;
        }
        reset($picture_list->container);

        // assign current_pid
        $current_pid = (is_null($current_pid)) ? current($picture_list->container) : $current_pid;

        foreach ($picture_list as &$image) {
            if (isset($image->hidden) && $image->hidden)
            {
                $tmp = $displayed_gallery->display_settings['number_of_columns'];
                $image->style = ($tmp > 0) ? 'style="width:' . floor(100 / $tmp) . '%;display: none;"' : 'style="display: none;"';
            }
        }

        // find our gallery to build the new one on
        $orig_gallery = $gallery_map->find(current($picture_list->container)->galleryid);

        // create the 'gallery' object
        $gallery = new stdclass;
        $gallery->ID = $orig_gallery->$gallery_key;
        $gallery->show_slideshow = FALSE;
        $gallery->show_piclens = FALSE;
        $gallery->name = stripslashes($orig_gallery->name);
        $gallery->title = stripslashes($orig_gallery->title);
        $gallery->description = html_entity_decode(stripslashes($orig_gallery->galdesc));
        $gallery->pageid = $orig_gallery->pageid;
        $gallery->anchor = 'ngg-gallery-' . $orig_gallery->$gallery_key . '-' . $current_page;
        $gallery->displayed_gallery = &$displayed_gallery;
        $gallery->columns = @intval($displayed_gallery->display_settings['number_of_columns']);
        $gallery->imagewidth = ($gallery->columns > 0) ? 'style="width:' . floor(100 / $gallery->columns) . '%;"' : '';

        if (is_integer($gallery->ID))
        {
            if (!empty($displayed_gallery->display_settings['show_slideshow_link'])) {
                $gallery->show_slideshow = TRUE;
                $gallery->slideshow_link = $params['alternative_view_link_url'];
                $gallery->slideshow_link_text = $displayed_gallery->display_settings['alternative_view_link_text'];
            }

            if (!empty($displayed_gallery->display_settings['show_piclens_link'])) {
                $gallery->show_piclens = true;
                $gallery->piclens_link = $params['piclens_link'];
                $gallery->piclens_link_text = $displayed_gallery->display_settings['piclens_link_text'];
            }
        }

        $gallery = apply_filters('ngg_gallery_object', $gallery, 4);

        // build our array of things to return
        $return = array(
            'registry' => C_Component_Registry::get_instance(),
            'gallery'  => $gallery,
        );

        // single_image is an internally added flag
        if (!empty($params['single_image']))
        {
            $return['image'] = $picture_list[0];
        }
        else {
            $return['current'] = $current_pid;
            $return['images']  = $picture_list->container;
        }

        // this is expected to always exist
        if (!empty($params['pagination']))
        {
            $return['pagination'] = $params['pagination'];
        }
        else {
            $return['pagination'] = NULL;
        }

        if (!empty($params['pagination']->next))
            $return['next'] = $params['pagination']->next;
        if (!empty($params['pagination']->prev))
            $return['prev'] = $params['pagination']->prev;

        return $return;
    }

}
