<?php

/**
 * This class provides a lazy-loading wrapper to the NextGen-Legacy "nggImage" class for use in legacy style templates
 */
class C_Image_Wrapper
{
    public $_cache;         // cache of retrieved values
    public $_settings;      // I_Settings_Manager cache
    public $_storage;       // I_Gallery_Storage cache
    public $_galleries;     // cache of I_Gallery_Mapper (plural)
    public $_orig_image;    // original provided image
    public $_orig_image_id; // original image ID
    public $_cache_overrides; // allow for forcing variable values
    public $_legacy = FALSE;

    /**
     * Constructor. Converts the image class into an array and fills from defaults any missing values
     *
     * @param object $gallery Individual result from displayed_gallery->get_entities()
     * @param object $displayed_gallery Displayed gallery
     * @param bool $legacy Whether the image source is from NextGen Legacy or NextGen
     * @return void
     */
    public function __construct($image, $displayed_gallery, $legacy = FALSE)
    {
        // for clarity
        if (isset($displayed_gallery->display_settings['number_of_columns']))
        {
            $columns = $displayed_gallery->display_settings['number_of_columns'];
        }
        else {
            $columns = 0;
        }

        // Public variables
        $defaults = array(
            'errmsg'    => '',    // Error message to display, if any
            'error'     => FALSE, // Error state
            'imageURL'  => '',    // URL Path to the image
            'thumbURL'  => '',    // URL Path to the thumbnail
            'imagePath' => '',    // Server Path to the image
            'thumbPath' => '',    // Server Path to the thumbnail
            'href'      => '',    // A href link code

            // Mostly constant
            'thumbPrefix' => 'thumbs_',  // FolderPrefix to the thumbnail
            'thumbFolder' => '/thumbs/', // Foldername to the thumbnail

            // Image Data
            'galleryid'   => 0,  // Gallery ID
            'pid'         => 0,  // Image ID
            'filename'    => '', // Image filename
            'description' => '', // Image description
            'alttext'     => '', // Image alttext
            'imagedate'   => '', // Image date/time
            'exclude'     => '', // Image exclude
            'thumbcode'   => '', // Image effect code

            // Gallery Data
            'name'       => '', // Gallery name
            'path'       => '', // Gallery path
            'title'      => '', // Gallery title
            'pageid'     => 0, // Gallery page ID
            'previewpic' => 0,  // Gallery preview pic

            'style'     => ($columns > 0) ? 'style="width:' . floor(100 / $columns) . '%;"' : '',
            'hidden'    => FALSE,
            'permalink' => '',
            'tags'      => '',
        );

        // convert the image to an array and apply the defaults
        $this->_orig_image = $image;
        $image = (array)$image;
        foreach ($defaults as $key => $val) {
            if (!isset($image[$key]))
            {
                $image[$key] = $val;
            }
        }

        // cache the results
        ksort($image);
        $this->_cache = $image;
        $id_field = (!empty($image['id_field']) ? $image['id_field'] : 'pid');
        $this->_orig_image_id = $image[$id_field];

        $this->_legacy = $legacy;
    }

    public function __set($name, $value)
    {
        $this->_cache[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->_cache[$name]);
    }

    public function __unset($name)
    {
        unset($this->_cache[$name]);
    }

    /**
     * Lazy-loader for image variables.
     *
     * @param string $name Parameter name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_cache_overrides[$name]))
        {
            return $this->_cache_overrides[$name];
        }

        // at the bottom we default to returning $this->_cache[$name].
        switch ($name)
        {
            case 'alttext':
                $this->_cache['alttext'] = (empty($this->_cache['alttext'])) ?  ' ' : html_entity_decode(stripslashes(nggGallery::i18n($this->_cache['alttext'], 'pic_' . $this->__get('id') . '_alttext')));
                return $this->_cache['alttext'];

            case 'author':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['author'] = $gallery->name;
                return $this->_cache['author'];

            case 'caption':
                $caption = html_entity_decode(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('id') . '_description')));
                if (empty($caption))
                {
                    $caption = '&nbsp;';
                }
                $this->_cache['caption'] = $caption;
                return $this->_cache['caption'];

            case 'description':
                $this->_cache['description'] = (empty($this->_cache['description'])) ? ' ' : html_entity_decode(stripslashes(nggGallery::i18n($this->_cache['description'], 'pic_' . $this->__get('id') . '_description')));
                return $this->_cache['description'];

            case 'galdesc':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['galdesc'] = $gallery->name;
                return $this->_cache['galdesc'];

            case 'gid':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['gid'] = $gallery->name;
                return $this->_cache['gid'];

            case 'href':
                return $this->__get('imageHTML');
            
            case 'id':
                return $this->_orig_image_id;

            case 'imageHTML':
                $tmp  = '<a href="' . $this->__get('imageURL') . '" title="'
                      . htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('id') . '_description')))
                      . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->__get('alttext')
                      . '" src="' . $this->__get('imageURL') . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['imageHTML'] = $tmp;
                return $this->_cache['imageHTML'];

            case 'imagePath':
                $storage = $this->get_storage();
                $this->_cache['imagePath'] = $storage->get_image_abspath($this->_orig_image, 'full');
                return $this->_cache['imagePath'];

            case 'imageURL':
                $storage = $this->get_storage();
                $this->_cache['imageURL'] = $storage->get_image_url($this->_orig_image, 'full');
                return $this->_cache['imageURL'];

            case 'linktitle':
                $this->_cache['linktitle'] = htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('id') . '_description')));
                return $this->_cache['linktitle'];

            case 'name':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['name'] = $gallery->name;
                return $this->_cache['name'];

            case 'pageid':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['pageid'] = $gallery->name;
                return $this->_cache['pageid'];

            case 'path':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['path'] = $gallery->name;
                return $this->_cache['path'];

            case 'permalink':
                $this->_cache['permalink'] = $this->__get('imageURL');
                return $this->_cache['permalink'];

            case 'pid':
                return $this->_orig_image_id;

            case 'pidlink':
                $application = C_Component_Registry::get_instance()->get_utility('I_Router')->get_routed_app();
                $controller = C_Component_Registry::get_instance()->get_utility('I_Display_Type_Controller');
                $this->_cache['pidlink'] = $controller->set_param_for(
                    $application->get_routed_url(TRUE),
                    'pid',
                    $this->__get('image_slug')
                );
                return $this->_cache['pidlink'];

            case 'previewpic':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['previewpic'] = $gallery->name;
                return $this->_cache['previewpic'];

            case 'size':
                $w = $this->_orig_image->meta_data['thumbnail']['width'];
                $h = $this->_orig_image->meta_data['thumbnail']['height'];
                return "width='{$w}' height='{$h}'";

            case 'slug':
                if ($this->_legacy)
                {
                    $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                }
                else {
                    $gallery_map = $this->get_gallery($this->__get('galleryid'));
                    $gallery = $gallery_map->find($this->__get('galleryid'));
                }
                $this->_cache['slug'] = $gallery->name;
                return $this->_cache['slug'];

            case 'tags':
                $this->_cache['tags'] = wp_get_object_terms($this->__get('id'), 'ngg_tag', 'fields=all');
                return $this->_cache['tags'];

            case 'thumbHTML':
                $tmp = '<a href="' . $this->__get('imageURL') . '" title="'
                     . htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('id') . '_description')))
                     . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->__get('alttext')
                     . '" src="' . $this->thumbURL . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['thumbHTML'] = $tmp;
                return $this->_cache['thumbHTML'];

            case 'thumbPath':
                $storage = $this->get_storage();
                $this->_cache['thumbPath'] = $storage->get_image_abspath($this->_orig_image, 'thumbnail');
                return $this->_cache['thumbPath'];

            case 'thumbnailURL':
                $storage = $this->get_storage();
                $this->_cache['thumbnailURL'] = $storage->get_thumb_url($this->_orig_image);
                return $this->_cache['thumbnailURL'];

            case 'thumbcode':
                $this->_cache['thumbcode'] = $this->get_thumbcode($this->__get('name'));
                return $this->_cache['thumbcode'];

            case 'thumbURL':
                return $this->__get('thumbnailURL');

            case 'title':
                $this->_cache['title'] = stripslashes($this->__get('name'));
                return $this->_cache['title'];

            case 'url':
                $storage = $this->get_storage();
                $this->_cache['url'] = $storage->get_image_url($this->_orig_image, 'full');
                return $this->_cache['url'];

            default:
                return $this->_cache[$name];
        }
    }

    // called on initial nggLegacy image at construction. not sure what to do with it now.
    function construct_ngg_Image($gallery)
    {
        do_action_ref_array('ngg_get_image', array(&$this));
        unset($this->tags);
    }

    /**
     * Retrieves and caches an I_Settings_Manager instance
     *
     * @return mixed
     */
    function get_settings()
    {
        if (is_null($this->_settings))
        {
            $this->_settings = C_Component_Registry::get_instance()->get_utility('I_Settings_Manager');
        }
        return $this->_settings;
    }

    /**
     * Retrieves and caches an I_Gallery_Storage instance
     *
     * @return mixed
     */
    function get_storage()
    {
        if (is_null($this->_storage))
        {
            $this->_storage = C_Component_Registry::get_instance()->get_utility('I_Gallery_Storage');
        }
        return $this->_storage;
    }

    /**
     * Retrieves I_Gallery_Mapper instance.
     *
     * @param int $gallery_id Gallery ID
     * @return mixed
     */
    function get_gallery($gallery_id)
    {
        if (isset($this->container) && method_exists($this->container, 'get_gallery'))
        {
            return $this->container->get_gallery($gallery_id);
        }
        $gallery_map = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
        return $gallery_map->find($gallery_id);
    }

    /**
     * Retrieves I_Gallery_Mapper instance.
     *
     * @param int $gallery_id Gallery ID
     * @return mixed
     */
    function get_legacy_gallery($gallery_id)
    {
        return C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper')->find($gallery_id);
    }

    /**
    * Get the thumbnail code (to add effects on thumbnail click)
    *
    * Applies the filter 'ngg_get_thumbcode'
    */
    function get_thumbcode($gallery_name = '')
    {
        $settings = $this->get_settings();

        // clean up the name
        $gallery_name = sanitize_title($gallery_name);

        // get the effect code
        if ('none' != $settings->get('thumbEffect'))
        {
            $this->_cache['thumbcode'] = stripslashes($settings->get('thumbCode'));
        }

        // for highslide to a different approach
        if ('highslide' == $settings->get('thumbEffect'))
        {
            $this->_cache['thumbcode'] = str_replace('%GALLERY_NAME%', "'{$gallery_name}'", $this->_cache['thumbcode']);
        }
        else {
            $this->_cache['thumbcode'] = str_replace('%GALLERY_NAME%', $gallery_name, $this->_cache['thumbcode']);
        }

        return apply_filters('ngg_get_thumbcode', $this->_cache['thumbcode'], $this);
    }

    /**
     * For compatibility support
     *
     * @return mixed
     */
    function get_href_link()
    {
        return $this->__get('imageHTML');
    }

    /**
     * For compatibility support
     *
     * @return mixed
     */
    function get_href_thumb_link()
    {
        return $this->__get('thumbHTML');
    }

    /**
     * Function exists for legacy support but has been gutted to not do anything
     *
     * @param int $width
     * @param int $height
     * @param string $mode could be watermark | web20 | crop
     * @return the url for the image or false if failed
     */
    function cached_singlepic_file($width = '', $height = '', $mode = '' )
    {
        $dynthumbs = C_Component_Registry::get_instance()->get_utility('I_Dynamic_Thumbnails_Manager');
        $storage = $this->get_storage();

        // determine what to do with 'mode'
        $display_reflection = FALSE;
        $display_watermark  = FALSE;

        if (!is_array($mode))
            $mode = explode(',', $mode);
        if (in_array('web20', $mode))
            $display_reflection = TRUE;
        if (in_array('watermark', $mode))
            $display_watermark = TRUE;

        // and go for it
        $params = array(
            'width'      => $width,
            'height'     => $height,
            'watermark'  => $display_watermark,
            'reflection' => $display_reflection
        );

        return $storage->get_image_url((object)$this->_cache, $dynthumbs->get_size_name($params));
    }

    /**
     * Get the tags associated to this image
     */
    function get_tags()
    {
        return $this->__get('tags');
    }

    /**
     * Get the permalink to the image
     *
     * TODO: Get a permalink to a page presenting the image
     */
    function get_permalink()
    {
        return $this->__get('permalink');
    }

    /**
     * Returns the _cache array; used by nggImage
     * @return array
     */
    function _get_image()
    {
        return $this->_cache;
    }

}
