<?php

/**
 * This class provides a lazy-loading wrapper to the NextGen-Legacy "nggImage" class for use in legacy style templates
 */
class C_NextGen_Gallery_Image_Wrapper
{
    /** @var stdClass Container of image attributes used to copy already known data */
    public $_cache;

    /**
     * Constructor. Converts the image class into an array and fills from defaults any missing values
     *
     * @param object $gallery Individual result from displayed_gallery->get_images()
     * @return void
     */
    public function __construct($image)
    {
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

            'permalink' => '',
            'tags'      => '',
        );
        $image = (array)$image;
        foreach ($defaults as $key => $val) {
            if (!isset($image[$key]))
            {
                $image[$key] = $val;
            }
        }
        ksort($image);
        $this->_cache = $image;
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

    public function __get($name)
    {
        // at the bottom we default to returning $this->_cache[$name] (for now) as we assume the attribute
        // requested has been assigned and is provided by the image retrieved from our display gallery's get_images()
        // the other instances are fields that we'll lazy-load and fill in as requested
        switch ($name)
        {
            case 'author':
                break;

            case 'caption':
                break;

            case 'errmsg':
                break;

            case 'error':
                break;

            case 'galdesc':
                break;

            case 'gid':
                break;

            case 'hidden':
                break;

            case 'href':
                return $this->__get('imageHTML');
                break;

            case 'imageHTML':
                $tmp  = '<a href="' . $this->__get('imageURL') . '" title="'
                      . htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->__get('pid') . '_description')))
                      . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->__get('alttext')
                      . '" src="' . $this->__get('imageURL') . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['imageHTML'] = $tmp;
                return $this->_cache['imageHTML'];

            case 'imagePath':
                $this->_cache['imagePath'] = WINABSPATH  . $this->__get('path') . '/' . $this->__get('filename');
                return $this->_cache['imagePath'];

            case 'imageURL':
                $this->_cache['imageURL'] = site_url() . '/' . $this->__get('path') . '/' . $this->__get('filename');
                return $this->_cache['imageURL'];

            case 'name':
                break;

            case 'pageid':
                break;

            case 'path':
                break;

            case 'permalink':
                break;

            case 'pidlink':
                break;

            case 'previewpic':
                break;

            case 'size':
                break;

            case 'slug':
                break;

            case 'style':
                break;

            case 'thumbFolder':
                break;

            case 'thumbHTML':
                $tmp = '<a href="' . $this->__get('imageURL') . '" title="'
                     . htmlspecialchars(stripslashes(nggGallery::i18n($this->__get('description'), 'pic_' . $this->pid . '_description')))
                     . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->alttext
                     . '" src="' . $this->thumbURL . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['thumbHTML'] = $tmp;
                return $this->_cache['thumbHTML'];

            case 'thumbPath':
                $this->_cache['thumbPath'] = WINABSPATH . $this->__get('path') . '/thumbs/thumbs_'
                                           . $this->__get('filename');
                return $this->_cache['thumbPath'];

            case 'thumbPrefix':
                break;

            case 'thumbURL':
                $this->_cache['thumbURL'] = site_url() . '/' . $this->__get('path') . '/thumbs/thumbs_'
                                          . $this->__get('filename');
                return $this->_cache['thumbURL'];

            case 'thumbcode':
                break;

            case 'thumbnailURL':
                break;

            case 'title':
                break;

            case 'url':
                break;

            default:
                return $this->_cache[$name];
        }
    }

    function construct_ngg_Image($gallery)
    {
        $this->name       = $gallery->name;
        $this->path       = $gallery->path;
        $this->title      = stripslashes($gallery->title);
        $this->pageid     = $gallery->pageid;
        $this->previewpic = $gallery->previewpic;

        do_action_ref_array('ngg_get_image', array(&$this));

        // Note wp_cache_add will increase memory needs (4-8 kb)
        // wp_cache_add($this->pid, $this, 'ngg_image');
        // Get tags only if necessary
        unset($this->tags);
    }

    /**
    * Get the thumbnail code (to add effects on thumbnail click)
    *
    * Applies the filter 'ngg_get_thumbcode'
    */
    function get_thumbcode($gallery_name = '')
    {
        $settings = C_Component_Registry::get_instance()->get_utility('I_NextGen_Settings');

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
            $this->_cache['thumbcode'] = str_replace('%GALLERY_NAME%', "'{$gallery_name}'", $this->__get('thumbcode'));
        }
        else {
            $this->_cache['thumbcode'] = str_replace('%GALLERY_NAME%', $gallery_name, $this->__get('thumbcode'));
        }

        return apply_filters('ngg_get_thumbcode', $this->_cache['thumbcode'], $this);
    }

    function get_href_link()
    {
        return $this->__get('imageHTML');
    }

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
        if (!isset($this->_cache['tags']))
        {
            $this->_cache['tags'] = wp_get_object_terms($this->__get('pid'), 'ngg_tag', 'fields=all');
        }
        return $this->__get('tags');
    }

    /**
     * Get the permalink to the image
     *
     * TODO: Get a permalink to a page presenting the image
     */
    function get_permalink() {
        if ('' == $this->__get('permalink'))
        {
            $this->_cache['permalink'] = $this->__get('imageURL');
        }
        return $this->__get('permalink');
    }

}
