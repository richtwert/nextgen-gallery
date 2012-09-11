<?php

/**
 * Provides the ability to render a display type
 */
class Mixin_Render_Display_Type extends Mixin
{
	/**
	 * Displays a "displayed gallery" instance
	 *
	 * Alias Properties:
	 * gallery_ids/album_ids/tag_ids == container_ids
	 * image_ids/gallery_ids		 == entity_ids
	 *
	 * Default Behavior:
	 * - if order_by and order_direction are missing, the default settings
	 *   are used from the "Other Options" page. The exception to this is
	 *   when entity_ids are selected, in which the order is custom unless
	 *   specified.
	 *
	 * How to use:
	 *
	 * 1. To retrieve images from gallery #1 and #3, but exclude
	 * images 4,6:
	 * [ngg_images gallery_ids="1,3" exclusions="4,6" display_type="photocrati-nextgen_pro_thumbnails"]
	 *
	 * 2. To retrieve images matching tags "landscapes" and "wedding shoots":
	 * [ngg_images tag_ids="landscapes,wedding shoots" display_type="photocrati-nextgen_pro_thumbnails"]
	 *
	 * 3. To retrieve galleries from albums #1 & #2, but exclude gallery #1:
	 * [ngg_images album_ids="1,2" exclusions="1" display_type="photocrati-nextgen-basic-compact-album"]
	 *
	 * 4. To retrieve image #2, #3, and #5 - independent of what container is used
	 * [ngg_images image_ids="2,3,5" display_type="photocrati-nextgen_pro_thumbnails"]
	 *
	 * 5. To retrieve galleries #3 and #5, custom sorted, in album view
	 * [ngg_images source="albums" gallery_ids="3,5" display_type="photocrati-nextgen-basic-compact-album"]
	 *
	 * 6. To retrieve recent images, sorted by alt/title text
	 * [ngg_images source="recent" order_by="alttext" display_type="photocrati-nextgen_pro_thumbnails"]
	 *
	 * 7. To retrieve random image
	 * [ngg_images source="random" display_type="photocrati-nextgen_pro_thumbnails"]
	 */
	function display_images($params, $inner_content=NULL)
	{
		$displayed_gallery = NULL;

		// Get the NextGEN settings to provide some defaults
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		// Configure the arguments
		$defaults = array(
			'id'				=>	NULL,
			'source'			=>	'',
			'container_ids'		=>	array(),
			'gallery_ids'		=>	array(),
			'album_ids'			=>	array(),
			'tag_ids'			=>	array(),
			'display_type'		=>	'',
			'exclusions'		=>	array(),
			'order_by'			=>	$settings->galSort,
			'order_direction'	=>	$settings->galSortOrder,
			'image_ids'			=>	array(),
			'entity_ids'		=>	array()
		);
		$args = shortcode_atts($defaults, $params);

		// Are we loading a specific displayed gallery that's persisted?
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		if (!is_null($args['id'])) {
			$displayed_gallery = $mapper->find($args['id']);
			unset($mapper); // no longer needed
		}

		// We're generating a new displayed gallery
		else {

			// Perform some conversions...

			// Galleries?
			if ($args['gallery_ids']) {
				if ($args['source'] != 'albums' AND $args['source'] != 'album') {
					$args['source']					= 'galleries';
					$args['container_ids']		= $args['gallery_ids'];
				}
				elseif ($args['source'] == 'albums') {
					$args['entity_ids']	= $args['gallery_ids'];
				}
				unset($args['gallery_ids']);
			}

			// Albums ?
			elseif ($args['album_ids']) {
				$args['source']					= 'albums';
				$args['container_ids']		= $args['album_ids'];
				unset($args['albums_ids']);
			}

			// Tags ?
			elseif ($args['tag_ids']) {
				$args['source']					= 'tags';
				$args['container_ids']		= $args['tag_ids'];
				unset($args['tag_ids']);
			}

			// Specific images selected
			elseif ($args['image_ids']) {
				$source = 'galleries';
				$entity_ids = $args['image_ids'];
				unset($args['image_ids']);
			}

			// Convert strings to arrays
			if (!is_array($args['container_ids'])) {
				$args['container_ids']	= preg_split("/,|\|/", $args['container_ids']);
			}
			if (!is_array($args['exclusions'])) {
				$args['exclusions']		= preg_split("/,|\|/", $args['exclusions']);
			}
			if (!is_array($args['entity_ids'])) {
				$args['entity_ids']		= preg_split("/,|\|/", $args['entity_ids']);
			}

			// Get the display settings
			foreach (array_keys($defaults) as $key) unset($params[$key]);
			$args['display_settings']	= $params;

			// Validate the displayed gallery
			$factory = $this->get_registry()->get_utility('I_Component_Factory');
			$displayed_gallery = $factory->create('displayed_gallery', $mapper, $args);
			unset($factory);
		}

		// Validate the displayed gallery
		if ($displayed_gallery && $displayed_gallery->validate()) {

			// Set a temporary id
			$displayed_gallery->id(uniqid('temp'));

			// Display!
			$this->render_displayed_gallery($displayed_gallery);
		}
		else return "Invalid Displayed Gallery".print_r($displayed_gallery->get_errors());
	}


	/**
	 * Renders a displayed gallery on the frontend
	 * @param C_Displayed_Gallery|stdClass $displayed_gallery
	 */
	function render_displayed_gallery($displayed_gallery, $return=FALSE)
	{
		// Display!
		$controller = $this->get_registry()->get_utility(
			'I_Display_Type_Controller', $displayed_gallery->display_type
		);
		$controller->enqueue_frontend_resources($displayed_gallery);
		return $controller->is_alternative_view_request() ?
			$controller->alternative_index($displayed_gallery, TRUE) :
			$controller->index($displayed_gallery, TRUE);
	}

    /**
     * Substitutes the gallery placeholder content with the gallery type frontend
     * view, returns a list of static resources that need to be loaded
     * @param stdClass $post
     */
    function substitute_placeholder_imgs($content)
    {
        // Load html into parser
        $doc = new simple_html_dom();
		if ($content) {
			$doc->load($content);

			// Find all placeholder images
			$imgs = $doc->find("img[class='ngg_displayed_gallery']");
			if ($imgs) {

				// Get the displayed gallery mapper
				$mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

				// Substitute each image for the gallery type frontent content
				foreach ($imgs as $img) {

					// The placeholder MUST have a gallery instance id
					$preview_url = preg_quote(PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL, '/');
					if (preg_match("/{$preview_url}\?id=(\d+)/", $img->src, $match)) {

						// Find the displayed gallery
						$displayed_gallery_id = $match[1];
						$displayed_gallery = $mapper->find($displayed_gallery_id, TRUE);

						// Get the content for the displayed gallery
						$content = '<p>'._('Invalid Displayed Gallery').'</p>';
						if ($displayed_gallery) {
							$content = $this->object->render_displayed_gallery($displayed_gallery, TRUE);
						}

						// Replace the placeholder with the displayed gallery content
						$img->outertext = $this->object->compress_html($content);
					}
				}
				$content = (string)$doc->save();
			}
			return $content;
		}
    }

    //  this function gets rid of tabs, line breaks, and white space
    function compress_html($html)
    {
        $html = preg_replace("/>\s+/", ">", $html);
        $html = preg_replace("/\s+</", "<", $html);
        $html = preg_replace("/<!--(?:(?!-->).)*-->/m", "", $html);
        return $html;
    }

    function _get_param($name, $default, $params)
    {
        return (isset($params[$name])) ? $params[$name] : $default;
    }

    function wrap_shortcode_album($params, $inner_content=NULL)
    {
        // not yet implemented
    }

    function wrap_shortcode_imagebrowser($params, $inner_content=NULL)
    {
        $params['image_ids']    = $this->_get_param('id', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_imagebrowser', $params);
        unset($params['id']);
        $this->object->display_images($params, $inner_content);
    }

    function wrap_shortcode_nggallery($params, $inner_content=NULL)
    {
        $params['gallery_ids']     = $this->_get_param('id', NULL, $params);
        $params['display_type']    = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        if (isset($params['images']))
        {
            $params['images_per_page'] = $this->_get_param('images', NULL, $params);
        }
        unset($params['id']);
        unset($params['images']);
        $this->object->display_images($params, $inner_content);
    }

    function wrap_shortcode_nggtags($params, $inner_content=NULL)
    {
        $params['tag_ids']      = $this->_get_param('gallery', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        unset($params['gallery']);
        $this->object->display_images($params, $inner_content);
    }

    function wrap_shortcode_random($params, $inner_content=NULL)
    {
        $params['source']             = $this->_get_param('source', 'random', $params);
        $params['images_per_page']    = $this->_get_param('max', NULL, $params);
        $params['disable_pagination'] = $this->_get_param('disable_pagination', TRUE, $params);
        $params['display_type']       = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);

        // inside if because Mixin_Displayed_Gallery_Instance_Methods->get_images() doesn't handle NULL container_ids
        // correctly
        if (isset($params['id']))
        {
            $params['container_ids'] = $this->_get_param('id', NULL, $params);
        }

        unset($params['max']);
        unset($params['id']);

        $this->object->display_images($params, $inner_content);
    }

    function wrap_shortcode_recent($params, $inner_content=NULL)
    {
        $params['source']             = $this->_get_param('source', 'recent', $params);
        $params['images_per_page']    = $this->_get_param('max', NULL, $params);
        $params['disable_pagination'] = $this->_get_param('disable_pagination', TRUE, $params);
        $params['display_type']       = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);

        if (isset($params['id']))
        {
            $params['container_ids'] = $this->_get_param('id', NULL, $params);
        }

        unset($params['max']);
        unset($params['id']);

        $this->object->display_images($params, $inner_content);
    }

    function wrap_shortcode_singlepic($params, $inner_content=NULL)
    {
        // not yet implemented
    }

    function wrap_shortcode_slideshow($params, $inner_content=NULL)
    {
        // not yet implemented
    }

    function wrap_shortcode_thumb($params, $inner_content=NULL)
    {
        $params['entity_ids']   = $this->_get_param('id', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        unset($params['id']);
        $this->object->display_images($params, $inner_content);
    }
}
