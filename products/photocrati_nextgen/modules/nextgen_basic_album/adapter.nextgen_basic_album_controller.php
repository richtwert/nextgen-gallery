<?php

class A_NextGen_Basic_Album_Controller extends Mixin
{

    function initialize()
    {
		$this->albums = array();
        $this->object->add_mixin('Mixin_NextGen_Basic_Templates');
        $this->object->add_mixin('Mixin_NextGen_Basic_Album_Settings');
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

		// We need to fetch the album containers selected in the Attach
		// to Post interface. We need to do this, because once we fetch the
		// included entities, we need to iterate over each entity and assign it
		// a parent_id, which is the album that it belongs to. We need to do this
		// because the link to the gallery, is not /nggallery/gallery--id, but
		// /nggallery/album--id/gallery--id

		// Are we to display a gallery?
        if ($gallery = $this->param('gallery'))
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
                    'source'				=> 'galleries',
                    'container_ids'			=> array($gallery),
                    'display_type'			=> $display_settings['gallery_display_type'],
					'original_display_type'	=> $displayed_gallery->display_type
                ),
                $return
            );
        }

		// If we're viewing a sub-album, then we use that album as a container instead
		else if (($album = $this->param('album'))) {
			// Are we to display a sub-album?
            if (!is_numeric($album))
            {
                $mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
                $result = array_pop($mapper->select()->where(array('slug = %s', $album))->limit(1)->run_query());
				$album = $result->{$result->id_field};
            }
            $displayed_gallery->entity_ids = array();
			$displayed_gallery->sortorder = array();
            $displayed_gallery->container_ids = ($album === '0' OR $album === 'all') ? array() : array($album);
			$displayed_gallery->debug = TRUE;
		}

		// Get the albums
		$this->albums = $displayed_gallery->get_albums();

        // None of the above: Display the main album. Get the settings required for display
        $current_page = (int)$this->param('page', 1);
        $offset = $display_settings['galleries_per_page'] * ($current_page - 1);
        $entities = $displayed_gallery->get_included_entities($display_settings['galleries_per_page'], $offset);

        // If there are entities to be displayed
        if ($entities)
        {
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

	/**
	 * Gets the parent album for the entity being displayed
	 * @param int $entity_id
	 * @return stdClass (album)
	 */
	function get_parent_album_for($entity_id)
	{
		$retval = NULL;

		foreach ($this->albums as $album) {
			if (in_array($entity_id, $album->sortorder)) {
				$retval = $album;
				break;
			}
		}

		return $retval;
	}


    function prepare_legacy_album_params($params)
    {
        $image_mapper           = $this->object->get_registry()->get_utility('I_Image_Mapper');
        $application            = $this->object->get_registry()->get_utility('I_Router')->get_routed_app();
        $storage                = $params['storage'];
        $image_gen              = $params['image_gen'];

        // legacy templates expect these dimensions
        $image_gen_params       = array(
            'width'             => 91,
            'height'            => 68,
            'crop'              => TRUE
        );

        // If pagination is not set, then set it to FALSE
        if (!isset($params['pagination']))
            $params['pagination'] = FALSE;

        // Transform entities
        $params['galleries']    = $params['entities'];
        unset($params['entities']);
        foreach ($params['galleries'] as &$gallery) {

            // Get the preview image url
           $gallery->previewurl = '';
            if ($gallery->previewpic && $gallery->previewpic > 0) {
                if (($image = $image_mapper->find(intval($gallery->previewpic)))) {
                    $gallery->previewurl    = $storage->get_image_url($image, $image_gen->get_size_name($image_gen_params));
                    $gallery->previewname   = $gallery->name;
                }
            }

            // Get the page link. If the entity is an album, then the url will
			// look like /nggallery/album--slug.
            $id_field = $gallery->id_field;
			if ($gallery->is_album) {
				$gallery->pagelink = $this->object->set_param_for(
					$this->object->get_routed_url(TRUE),
					'album',
					$gallery->slug
				);
			}

			// Otherwise, if it's a gallery then it will look like
			// /nggallery/album--slug/gallery--slug
			else {
				$pagelink = $this->object->get_routed_url(TRUE);
				$parent_album = $this->object->get_parent_album_for($gallery->$id_field);
				if ($parent_album) {
					$pagelink = $this->object->set_param_for(
						$pagelink,
						'album',
						$parent_album->slug
					);
				}
				$gallery->pagelink = $this->object->set_param_for(
					$pagelink,
					'gallery',
					$gallery->slug
				);
			}

			// The router by default will generate param segments that look like,
			// /gallery--foobar. We need to convert these to the admittingly
			// nicer links that ngglegacy uses
			$gallery->pagelink = $this->object->prettify_pagelink($gallery->pagelink);

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


	function prettify_pagelink($pagelink)
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		$regex = implode('', array(
			'#',
			'/(gallery|album)',
			preg_quote($settings->router_param_separator),
			'([^/?]+)',
			'#'
		));

		return preg_replace($regex, '/\2', $pagelink);
	}

    /**
     * Enqueues all static resources required by this display type
     *
     * @param C_Displayed_Gallery $displayed_gallery
     */
    function enqueue_frontend_resources($displayed_gallery)
    {
        wp_enqueue_style('nextgen_basic_album_style', $this->get_static_url('nextgen_basic_album#nextgen_basic_album.css'));
        $this->call_parent('enqueue_frontend_resources', $displayed_gallery);
    }

}
