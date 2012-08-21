<?php

class Mixin_NextGen_Basic_Templates extends Mixin
{

    /**
     * Renders 'template' settings field
     *
     * @param $display_type
     * @return mixed
     */
    function _render_nextgen_basic_templates_template_field($display_type)
    {
        // add a label to our files listing so the user can make an informed choice
        $files_available = $this->object->get_available_templates();
        $files_list = array();
        foreach ($files_available as $label => $files)
        {
            foreach ($files as $file) {
                $tmp = explode(DIRECTORY_SEPARATOR, $file);
                $files_list[] = "[{$label}]: " . end($tmp);
            }
        }
        $files_list = json_encode($files_list);

        wp_enqueue_script('jquery-ui-autocomplete');

        return $this->object->render_partial(
            'nextgen_basic_templates_settings_template',
            array(
                'files' => $files_list,
                'display_type_name' => $display_type->name,
                'template_label' => _('Template:'),
                'template' => $display_type->settings['template'],
            ),
            True
        );
    }

    /**
     * Returns an array of template storing directories
     *
     * @return array Template storing directories
     */
    function get_template_directories()
    {
        return array(
            'Overrides' => STYLESHEETPATH . DIRECTORY_SEPARATOR . 'nggallery' . DIRECTORY_SEPARATOR,
            'NextGen' => NGGALLERY_ABSPATH . 'view' . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Returns an array of all available template files
     *
     * @return array All available template files
     */
    function get_available_templates()
    {
        $files = array();
        foreach ($this->object->get_template_directories() as $label => $dir) {
            $tmp = $this->object->get_templates_from_dir($dir);
            if ($tmp)
            {
                $files[$label] = $tmp;
            }
        }
        return $files;
    }

    /**
     * Recursively scans $dir for files ending in .php
     *
     * @param string $dir Directory
     * @return array All php files in $dir
     */
    function get_templates_from_dir($dir)
    {
        if (!is_dir($dir))
        {
            return;
        }
        $dir = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir);
        $regex_iterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        $files = array();
        foreach ($regex_iterator as $filename) {
            $files[] = reset($filename);
        }
        return $files;
    }

    /**
     * Renders NextGen-Legacy style templates
     *
     * @param string $template_name File name
     * @param array $vars Specially formatted array of parameters
     * @param bool $callback
     */
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
            return;
        }

        $template_name = $template_name . '.php';

        foreach ($this->object->get_template_directories() as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $template_name))
            {
                include ($dir . DIRECTORY_SEPARATOR . $template_name);
                return;
            }
        }

        if ($callback === true)
        {
            echo "<p>Rendering of template {$template_name}.php failed</p>";
        }
        else {
            // test without the "-template" name one time more
            $template_name = array_shift(explode('-', $template_name , 2));
            $this->object->legacy_render($template_name, $vars , true);
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
