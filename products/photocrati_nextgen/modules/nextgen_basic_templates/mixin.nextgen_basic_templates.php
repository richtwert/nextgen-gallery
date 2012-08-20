<?php

class Mixin_NextGen_Basic_Templates extends Mixin
{

    function _render_nextgen_basic_templates_template_field($display_type)
    {
        return $this->object->render_partial(
            'nextgen_basic_templates_settings_template',
            array(
                'display_type_name' => $display_type->name,
                'template_label' => _('Template:'),
                'template' => $display_type->settings['template'],
            ),
            True
        );
    }

    function legacy_render($template_name, $vars = array(), $callback = false)
    {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        // hook into the render feature to allow other plugins to include templates
        $custom_template = apply_filters('ngg_render_template', false, $template_name);

        if (($custom_template != false) && file_exists($custom_template))
        {
            include($custom_template);
        }
        else if (file_exists(STYLESHEETPATH . "/nggallery/{$template_name}.php"))
        {
            include (STYLESHEETPATH . "/nggallery/{$template_name}.php");
        }
        else if (file_exists (NGGALLERY_ABSPATH . "/view/{$template_name}.php"))
        {
            include (NGGALLERY_ABSPATH . "/view/{$template_name}.php");
        }
        else if ($callback === true)
        {
            echo "<p>Rendering of template {$template_name}.php failed</p>";
        }
        else {
            // test without the "-template" name one time more
            $template_name = array_shift(explode('-', $template_name , 2));
            $this->render($template_name, $vars , true);
        }
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
    function prepare_legacy_parameters($images, $displayed_gallery, $slideshow_link, $piclens_link, $pagination)
    {
        // setup
        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        $nggpage = get_query_var('nggpage');
        $pageid  = get_query_var('pageid');
        $pid     = get_query_var('pid');

        $maxElement = $settings->galImages;

        $picture_list = array();
        $current_pid  = NULL;

        // begin processing
        $current_page = (get_the_ID() == FALSE) ? 0 : get_the_ID();

        // determine what the "current image" is; used mostly for carousel
        if (!is_numeric($pid) && !empty($pid))
        {
            $picture = $this->object->get_registry()
                                    ->get_utility('I_Gallery_Image_Mapper')
                                    ->find_first(array('image_slug = %s', $pid));
            $id_field = $picture->id_field;
            $pid = $picture->$id_field;
        }

        // create our new wrappers
        foreach ($images as $image) {
            $new_image = new C_NextGen_Gallery_Image_Wrapper($image, $displayed_gallery);
            if ($pid == $new_image->id)
            {
                $current_pid = $new_image;
            }
            $picture_list[] = $new_image;
        }
        reset($picture_list);

        // assign current_pid
        $current_pid = (is_null($current_pid)) ? current($picture_list) : $current_pid;

        // the entire next chunk is related to 'hidden images' support; I (BOwens) don't think it works ATM
        if ($maxElement > 0)
        {
            if (!is_home() || $pageid == $current_page)
            {
                $page = (!empty($nggpage)) ? (int)$nggpage : 1;
            }
            else {
                $page = 1;
            }
            $start = $offset = ($page - 1) * $maxElement;
            if (!$settings->galHiddenImg)
            {
                if ($start > 0 )
                {
                    array_splice($picture_list, 0, $start);
                }
                array_splice($picture_list, $maxElement);
            }
        }
        $index = 0;
        foreach ($picture_list as $image) {
            if ($maxElement > 0 && $settings->galHiddenImg)
            {
                if (($index < $start) || ($index > ($start + $maxElement -1)) ){
                    $image->hidden = true;
                    $tmp = intval($displayed_gallery->display_settings['number_of_columns']);
                    $image->style  = ($tmp > 0) ? 'style="width:' . floor(100 / $tmp) . '%;display: none;"' : 'style="display: none;"';
                }
                $index++;
            }
        }

        // find our gallery to build the new one on
        $gallery_map = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
        $orig_gallery = $gallery_map->find(current($picture_list)->galleryid);
        $id_field = $orig_gallery->id_field;

        // create the 'gallery' object
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
        $gallery->imagewidth = ($gallery->columns > 0) ? 'style="width:' . floor(100 / $gallery->columns) . '%;"' : '';

        if (is_integer($gallery->ID)) {
            if ($displayed_gallery->display_settings['show_slideshow_link']) {
                $gallery->show_slideshow = TRUE;
                $gallery->slideshow_link = $slideshow_link;
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
            'next' => $pagination->next,
            'prev' => $pagination->prev
        );
    }

}
