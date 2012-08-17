<?php

/**
 * This class provides a lazy-loading wrapper to the NextGen-Legacy "nggImage" class for use in legacy style templates
 */
class C_NextGen_Gallery_Image_Wrapper
{
    // Public variables
    var $errmsg    = '';    // Error message to display, if any
    var $error     = FALSE; // Error state
    var $imageURL  = '';    // URL Path to the image
    var $thumbURL  = '';    // URL Path to the thumbnail
    var $imagePath = '';    // Server Path to the image
    var $thumbPath = '';    // Server Path to the thumbnail
    var $href      = '';    // A href link code

    // TODO: remove thumbPrefix and thumbFolder (constants)
    var $thumbPrefix = 'thumbs_';  // FolderPrefix to the thumbnail
    var $thumbFolder = '/thumbs/'; // Foldername to the thumbnail

    // Image Data
    var $galleryid   = 0;  // Gallery ID
    var $pid         = 0;  // Image ID
    var $filename    = ''; // Image filename
    var $description = ''; // Image description
    var $alttext     = ''; // Image alttext
    var $imagedate   = ''; // Image date/time
    var $exclude     = ''; // Image exclude
    var $thumbcode   = ''; // Image effect code

    // Gallery Data
    var $name       = ''; // Gallery name
    var $path       = ''; // Gallery path
    var $title      = ''; // Gallery title
    var $pageid     = 0;  // Gallery page ID
    var $previewpic = 0;  // Gallery preview pic

    var $permalink = '';
    var $tags      = '';

    /** @var stdClass Container of image attributes used to copy already known data */
    public $_cached_image;

    /**
     * Constructor
     *
     * @param object $gallery The nggGallery object representing the gallery containing this image
     * @return void
     */
    function __construct($image)
    {
        $this->_cached_image = $image;
    }

    function __get($name)
    {
        // at the bottom we default to returning $this->_cached_image->$name (for now) as we assume the attribute
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
                break;
            case 'imageHTML':
                $this->imageHTML = $this->get_href_link();
                return $this->imageHTML;
            case 'imagePath':
                $this->imagePath = WINABSPATH . $this->path . '/' . $this->filename;
                return $this->imagePath;
            case 'imageURL':
                $this->imageURL = site_url() . '/' . $this->path . '/' . $this->filename;
                return $this->imageURL;
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
                $this->thumbHTML = $this->get_href_thumb_link();
                return $this->thumbHTML;
            case 'thumbPath':
                $this->thumbPath = WINABSPATH . $this->path . '/thumbs/thumbs_' . $this->filename;
                return $this->thumbPath;
            case 'thumbPrefix':
                break;
            case 'thumbURL':
                $this->thumbURL  = site_url() . '/' . $this->path . '/thumbs/thumbs_' . $this->filename;
                return $this->thumbURL;
            case 'thumbcode':
                break;
            case 'thumbnailURL':
                break;
            case 'title':
                break;
            case 'url':
                break;
            default:
                return $this->_cached_image->$name;
        }
    }

    function construct_ngg_Image($gallery)
    {
        $gallery = (object)$gallery;
        foreach ($gallery as $key => $value) {
            $this->$key = $value;
        }

        // TODO: how to get these attributes

        // Finish initialisation
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

        print "<h1>THIS:</h1>";
        print var_dump($this);
        print "<hr/>";
    }

    /**
    * Get the thumbnail code (to add effects on thumbnail click)
    *
    * Applies the filter 'ngg_get_thumbcode'
    */
    function get_thumbcode($galleryname = '')
    {
        $settings = C_Component_Registry::get_instance()->get_utility('I_NextGen_Settings');

        // clean up the name
        $galleryname = sanitize_title($galleryname);

        // get the effect code
        if ('none' != $settings->get('thumbEffect'))
        {
            $this->thumbcode = stripslashes($settings->get('thumbCode'));
        }

        // for highslide to a different approach
        if ('highslide' == $settings->get('thumbEffect'))
        {
            $this->thumbcode = str_replace('%GALLERY_NAME%', "'{$galleryname}'", $this->thumbcode);
        }
        else {
            $this->thumbcode = str_replace('%GALLERY_NAME%', $galleryname, $this->thumbcode);
        }

        return apply_filters('ngg_get_thumbcode', $this->thumbcode, $this);
    }

    function get_href_link()
    {
        // create the a href link from the picture
        // $this->href  = "\n" . '<a href="' . $this->imageURL . '" title="' . htmlspecialchars(stripslashes(nggGallery::i18n($this->description, 'pic_' . $this->pid . '_description'))) . '" ' . $this->get_thumbcode($this->name) . '>' . "\n\t";
        // $this->href .= '<img alt="' . $this->alttext . '" src="' . $this->imageURL . '"/>' . "\n" . '</a>' . "\n";
        return $this->href;
    }

    function get_href_thumb_link()
    {
        // create the a href link with the thumbanil
        // $this->href  = "\n" . '<a href="' . $this->imageURL . '" title="' . htmlspecialchars(stripslashes(nggGallery::i18n($this->description, 'pic_' . $this->pid . '_description'))) . '" ' . $this->get_thumbcode($this->name) . '>' . "\n\t";
        // $this->href .= '<img alt="'.$this->alttext.'" src="'.$this->thumbURL.'"/>'."\n".'</a>'."\n";
        return $this->href;
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
        if (!isset($this->tags))
        {
            $this->tags = wp_get_object_terms($this->pid, 'ngg_tag', 'fields=all');
        }
        return $this->tags;
    }

    /**
     * Get the permalink to the image
     *
     * TODO: Get a permalink to a page presenting the image
     */
    function get_permalink() {
        if ('' == $this->permalink)
        {
            $this->permalink = $this->imageURL;
        }
        return $this->permalink;
    }

}
