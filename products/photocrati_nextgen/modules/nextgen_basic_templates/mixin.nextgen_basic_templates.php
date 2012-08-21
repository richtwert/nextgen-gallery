<?php

class Mixin_NextGen_Basic_Templates extends Mixin
{
    /**
     * Adds required JS libraries for the admin side
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'enqueue_backend_resources',
            'Enqueue Template Settings Resources for the Backend',
            get_class($this),
            '_enqueue_resources_for_settings'
        );
    }

    /**
     * Enqueues resources needed for template settings display
     *
     * @param type $displayed_gallery
     */
    function _enqueue_resources_for_settings($displayed_gallery)
    {
        wp_enqueue_script(
            'ngg_template_settings',
            PHOTOCRATI_GALLERY_MODULE_URL . DIRECTORY_SEPARATOR
                . basename(__DIR__) . DIRECTORY_SEPARATOR . 'js'
                . DIRECTORY_SEPARATOR . 'ngg_template_settings.js',
            array('jquery-ui-autocomplete') // deps
        );
    }

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
    function get_available_templates($prefix = FALSE)
    {
        $files = array();
        foreach ($this->object->get_template_directories() as $label => $dir) {
            $tmp = $this->object->get_templates_from_dir($dir, $prefix);
            if (!$tmp) { continue; }
            $files[$label] = $tmp;
        }
        return $files;
    }

    /**
     * Recursively scans $dir for files ending in .php
     *
     * @param string $dir Directory
     * @return array All php files in $dir
     */
    function get_templates_from_dir($dir, $prefix = FALSE)
    {
        if (!is_dir($dir))
        {
            return;
        }

        $dir = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir);

        // convert single-item arrays to string
        if (is_array($prefix) && count($prefix) <= 1)
        {
            $prefix = end($prefix);
        }

        // we can filter results by allowing a set of prefixes, one prefix, or by showing all available files
        if (is_array($prefix))
        {
            $str = implode('|', $prefix);
            $regex_iterator = new RegexIterator($iterator, "/({$str})-.+\.php$/i", RecursiveRegexIterator::GET_MATCH);
        }
        elseif (is_string($prefix))
        {
            $regex_iterator = new RegexIterator($iterator, "/{$prefix}-.+\.php$/i", RecursiveRegexIterator::GET_MATCH);
        }
        else {
            $regex_iterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        }

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
    function legacy_render($template_name, $vars = array())
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
    function prepare_legacy_parameters($images, $displayed_gallery, $pagination, $slideshow_link = False, $piclens_link = False)
    {
        // setup
        $settings	  = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$image_map	  = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
		$gallery_map  = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
		$image_key	  = $image_map->get_primary_key_column();
		$gallery_key  = $gallery_map->get_primary_key_column();

        $nggpage = get_query_var('nggpage');
        $pageid  = get_query_var('pageid');
        $pid     = get_query_var('pid');

        $maxElement = $settings->galImages;

        $picture_list = new C_NextGen_Gallery_Image_Wrapper_Collection();
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
            $new_image = new C_NextGen_Gallery_Image_Wrapper($image, $displayed_gallery);
            if ($pid == $new_image->$image_key)
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
                    array_splice($picture_list->container, 0, $start);
                }
                array_splice($picture_list->container, $maxElement);
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
        $orig_gallery = $gallery_map->find(current($picture_list)->galleryid);

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
            'registry' => C_Component_Registry::get_instance(),
            'pagination' => $pagination,
            'gallery' => $gallery,
            'images' => $picture_list->container,
            'current' => $current_pid,
            'next' => $pagination->next,
            'prev' => $pagination->prev
        );
    }

}
