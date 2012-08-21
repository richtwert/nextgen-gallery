<?php

/**
 * This class provides a lazy-loading wrapper to the NextGen-Legacy "nggImage" class for use in legacy style templates
 */
class C_NextGen_Gallery_Image_Wrapper
{
    public $_cache;         // cache of retrieved values
    public $_settings;      // I_NextGen_Settings cache
    public $_storage;       // I_Gallery_Storage cache
    public $_galleries;     // cache of I_Gallery_Mapper (plural)
    public $_orig_image;    // original provided image
    public $_orig_image_id; // original image ID

    /**
     * Constructor. Converts the image class into an array and fills from defaults any missing values
     *
     * @param object $gallery Individual result from displayed_gallery->get_images()
     * @return void
     */
    public function __construct($image, $displayed_gallery)
    {
        // for clarity
        $columns = $displayed_gallery->display_settings['number_of_columns'];

        // Public variables
        $defaults = array(
            'errmsg'    => '',    // Error message to display, if any
            'error'     => FALSE, // Error state
            'imageURL'  => '',    // URL Path to the image
            'thumbURL'  => '',    // URL Path to the thumbnail
            'imagePath' => '',    // Server Path to the image
            'thumbPath' => '',    // Server Path to the thumbnail
            'href'      => '',    // A href link code

            // TODO: remove thumbPrefix and thumbFolder (constants)
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
        $id_field = $image['id_field'];
        $this->_orig_image_id = $image[$id_field];
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
        // at the bottom we default to returning $this->_cache[$name].
        switch ($name)
        {
            case 'author':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
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

            case 'galdesc':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['galdesc'] = $gallery->name;
                return $this->_cache['galdesc'];

            case 'gid':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
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
                $this->_cache['imagePath'] = WINABSPATH  . $this->__get('path') . '/' . $this->__get('filename');
                return $this->_cache['imagePath'];

            case 'imageURL':
                $storage = $this->get_storage();
                $this->_cache['imageURL'] = $storage->get_image_url($this->_orig_image);
                return $this->_cache['imageURL'];

            case 'name':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['name'] = $gallery->name;
                return $this->_cache['name'];

            case 'pageid':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['pageid'] = $gallery->name;
                return $this->_cache['pageid'];

            case 'path':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['path'] = $gallery->name;
                return $this->_cache['path'];

            case 'permalink':
                $this->_cache['permalink'] = $this->__get('imageURL');
                return $this->_cache['permalink'];

            case 'pid':
                return $this->_orig_image_id;

            case 'pidlink':
                // only needed for carousel template
                $settings = $this->get_settings();
                $nggpage = get_query_var('nggpage');
                $post = &get_post(get_the_ID());
                $url = trailingslashit(get_permalink($post->ID)) . $settings->permalinkSlug;

                if (!empty($nggpage))
                {
                    $url .= '/page-' . $nggpage;
                }

                if (TRUE == $settings->usePermalinks)
                {
                    $url .= '/image/' . $this->__get('slug');
                }
                else {
                    $url .= '/image/' . $this->__get('id');
                }
                $this->_cache['pidlink'] = $url;
                return $this->_cache['pidlink'];

            case 'previewpic':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['previewpic'] = $gallery->name;
                return $this->_cache['previewpic'];

            case 'size':
                $w = $this->_orig_image->meta_data->thumbnail['width'];
                $h = $this->_orig_image->meta_data->thumbnail['height'];
                return "width='{$w}' height='{$h}'";

            case 'slug':
                $gallery_map = $this->get_gallery($this->__get('galleryid'));
                $gallery = $gallery_map->find($this->__get('galleryid'));
                $this->_cache['slug'] = $gallery->name;
                return $this->_cache['slug'];

            case 'tags':
                $this->_cache['tags'] = wp_get_object_terms($this->__get('id'), 'ngg_tag', 'fields=all');
                return $this->_cache['tags'];

            case 'thumbHTML':
                $tmp = '<a href="' . $this->__get('imageURL') . '" title="'
                     . htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('id') . '_description')))
                     . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->alttext
                     . '" src="' . $this->thumbURL . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['thumbHTML'] = $tmp;
                return $this->_cache['thumbHTML'];

            case 'thumbPath':
                $this->_cache['thumbPath'] = $this->__get('thumbnailURL');
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
                $this->_cache['url'] = $storage->get_image_url($this->_orig_image);
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
     * Retrieves and caches an I_NextGen_Settings instance
     *
     * @return mixed
     */
    function get_settings()
    {
        if (is_null($this->_settings))
        {
            $this->_settings = C_Component_Registry::get_instance()->get_utility('I_NextGen_Settings');
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
    function cached_singlepic_file($width = '', $height = '', $mode = '' ) {
        return FALSE;
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
    function get_permalink() {
        return $this->__get('permalink');
    }

}
