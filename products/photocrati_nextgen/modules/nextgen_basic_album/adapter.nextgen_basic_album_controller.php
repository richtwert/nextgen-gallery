<?php

class A_NextGen_Basic_Album_Controller extends Mixin
{

    function initialize()
    {
        $this->object->add_mixin('Mixin_NextGen_Basic_Templates');
        $this->object->add_mixin('Mixin_NextGen_Basic_Album_Settings');
        $this->object->add_mixin('Mixin_Thumbnail_Display_Type_Controller');
    }

    /**
     * Renders the front-end for the NextGen Basic Album display type
     *
     * @param $displayed_gallery
     * @param bool $return
     */
    function index_action($displayed_gallery, $return = FALSE)
    {
        $display_settings = $displayed_gallery->display_settings;

        // Are we to display a gallery?
        if ($gallery = get_query_var('gallery'))
        {
            // basic albums only support one per post
            if (isset($GLOBALS['nggShowGallery']))
                return;
            $GLOBALS['nggShowGallery'] = TRUE;

            if (!is_numeric($gallery))
            {
                $mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
                $result = reset($mapper->select()->where(array('slug = %s', $gallery))->limit(1)->run_query());
                $gallery = $result->{$result->id_field};
            }

            $renderer = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
            return $renderer->display_images(
                array(
                    'source'        => 'galleries',
                    'container_ids' => array($gallery),
                    'display_type'  => $display_settings['gallery_display_type']
                ),
                $return
            );
        }

        elseif (($album = get_query_var('album')))
        {
            // basic albums only support one per post
            if (isset($GLOBALS['nggShowGallery']))
                return;
            $GLOBALS['nggShowGallery'] = TRUE;

            // Are we to display a sub-album?
            if (!is_numeric($album))
            {
                $mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
                $result = reset($mapper->select()->where(array('slug = %s', $album))->limit(1)->run_query());
                $album = $result->{$result->id_field};
            }

            $displayed_gallery->entity_ids = array();
            $displayed_gallery->container_ids = ($album === '0' OR $album === 'all') ? array() : array($album);
        }

        // None of the above: Display the main album. Get the settings required for display
        $current_page = get_query_var('nggpage') ? get_query_var('nggpage') : (isset($_GET['nggpage']) ? intval($_GET['nggpage']) : 1);
        $offset = $display_settings['galleries_per_page'] * ($current_page - 1);
        $total = $displayed_gallery->get_entity_count();
        $entities = $displayed_gallery->get_included_entities($display_settings['galleries_per_page'], $offset);

        // If there are entities to be displayed
        if ($entities)
        {
            //  Create pagination
            if ($display_settings['galleries_per_page'] && !$display_settings['disable_pagination'])
            {
                $pagination = new nggNavigation;
                $display_settings['pagination'] = $pagination->create_navigation(
                    $current_page,
                    $total,
                    $display_settings['galleries_per_page']
                );
            }

            // Add additional parameters
            $display_settings['image_gen']    = &$this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
            $display_settings['current_page'] = $current_page;
            $display_settings['entities']     = &$entities;
            $display_settings['storage']      = &$this->object->get_registry()->get_utility('I_Gallery_Storage');

            // Render legacy template
            $display_settings = $this->prepare_legacy_album_params($display_settings);
            return $this->legacy_render($display_settings['template'], $display_settings, $return, 'album');

        }
        else {
            return $this->object->render_partial('no_images_found');
        }
    }


    function prepare_legacy_album_params($params)
    {
        $image_mapper           = $this->object->get_registry()->get_utility('I_Image_Mapper');
        $storage                = $params['storage'];
        $image_gen              = $params['image_gen'];

        // legacy templates expect these dimensions
        $image_gen_params       = array(
            'width'             => 91,
            'height'            => 68,
            'crop'              => TRUE
        );

        // If pagination is not set, then set it to FALSE
        if (!isset($params['pagination'])) $params['pagination'] = FALSE;

        // Transform entities
        $params['galleries']    = $params['entities'];
        unset($params['entities']);
        foreach ($params['galleries'] as &$gallery) {
			$gallery->title		= stripslashes($gallery->title);
			$gallery->name		= stripslashes($gallery->name);
			$gallery->galdesc	= stripslashes($gallery->galdesc);

            // Get the preview image url
           $gallery->previewurl = '';
            if ($gallery->previewpic && $gallery->previewpic > 0) {
                if (($image = $image_mapper->find(intval($gallery->previewpic)))) {
                    $gallery->previewurl    = $storage->get_image_url($image, $image_gen->get_size_name($image_gen_params));
                    $gallery->previewname   = $gallery->name;
                }
            }

            // Get the page link
            $uri = $_SERVER['REQUEST_URI'];
            $uri = remove_query_arg('gallery', $uri);
            $uri = urldecode(remove_query_arg('album', $uri));
            $id_field = $gallery->id_field;
            $gallery->pagelink = add_query_arg((empty($gallery->is_album) ? 'album' : 'gallery'), $gallery->$id_field);

            // Let plugins modify the gallery
            $gallery = apply_filters('ngg_album_galleryobject', $gallery);
        }

        // Clean up
        unset($storage);
        unset($image_mapper);
        unset($image_gen);
        unset($image_gen_params);

        return $params;
    }
}
