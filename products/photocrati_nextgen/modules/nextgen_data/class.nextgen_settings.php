<?php

/**
 * Holds a static array of the default NextGen Wordpress options
 */
class C_NextGen_Settings_Defaults
{
    /**
     * Returns default Wordpress options array
     *
     * @static
     * @param bool $global Returns global (multisite) options if True
     * @return array
     */
    public static function get_defaults($global = False) {

        /*
         * The global options are returned after the if()
         */
        if (False == $global)
        {
            return array(
                'gallerypath'    => 'wp-content/gallery/',
                'deleteImg'      => True,              // delete Images
                'swfUpload'      => True,              // activate the batch upload
                'usePermalinks'  => False,             // use permalinks for parameters
                'permalinkSlug'  => 'nggallery',       // the default slug for permalinks
                'graphicLibrary' => 'gd',              // default graphic library
                'imageMagickDir' => '/usr/local/bin/', // default path to ImageMagick
                'useMediaRSS'    => False,             // activate the global Media RSS file
                'usePicLens'     => False,             // activate the PicLens Link for galleries

                // Tags / categories
                'activateTags' => False,  // append related images
                'appendType'   => 'tags', // look for category or tags
                'maxImages'    => 7,      // number of images toshow

                // Thumbnail Settings
                'thumbwidth'   => 100,  // Thumb Width
                'thumbheight'  => 75,   // Thumb height
                'thumbfix'     => True, // Fix the dimension
                'thumbquality' => 100,  // Thumb Quality

                // Image Settings
                'imgWidth'      => 800,   // Image Width
                'imgHeight'     => 600,   // Image height
                'imgQuality'    => 85,    // Image Quality
                'imgBackup'     => True,  // Create a backup
                'imgAutoResize' => False, // Resize after upload

                // Gallery Settings
                'galImages'         => '20', // Number of images per page
                'galPagedGalleries' => 0,    // Number of galleries per page (in a album)
                'galColumns'        => 0,    // Number of columns for the gallery
                'galShowSlide'      => True, // Show slideshow
                'galTextSlide'      => __('[Show as slideshow]', 'nggallery'), // Text for slideshow
                'galTextGallery'    => __('[Show picture list]', 'nggallery'), // Text for gallery
                'galShowOrder'      => 'gallery',   // Show order
                'galSort'           => 'sortorder', // Sort order
                'galSortDir'        => 'ASC',       // Sort direction
                'galNoPages'        => True,        // use no subpages for gallery
                'galImgBrowser'     => False,       // Show ImageBrowser => instead effect
                'galHiddenImg'      => False,       // For paged galleries we can hide image
                'galAjaxNav'        => False,       // AJAX Navigation for Shutter effect

                // Thumbnail Effect
                'thumbEffect'  => 'shutter',                           // select effect
                'thumbCode'    => 'class="shutterset_%GALLERY_NAME%"', //

                // Watermark settings
                'wmPos'    => 'botRight',             // Postion
                'wmXpos'   => 5,                      // X Pos
                'wmYpos'   => 5,                      // Y Pos
                'wmType'   => 'text',                 // Type : 'image' / 'text'
                'wmPath'   => '',                     // Path to image
                'wmFont'   => 'arial.ttf',            // Font type
                'wmSize'   => 10,                     // Font Size
                'wmText'   => get_option('blogname'), // Text
                'wmColor'  => '000000',               // Font Color
                'wmOpaque' => '100',                  // Font Opaque

                // Image Rotator settings
                'enableIR'          => False,
                'slideFx'           => 'fade',
                'irURL'             => '',
                'irXHTMLvalid'      => False,
                'irAudio'           => '',
                'irWidth'           => 320,
                'irHeight'          => 240,
                'irShuffle'         => True,
                'irLinkfromdisplay' => True,
                'irShownavigation'  => False,
                'irShowicons'       => False,
                'irWatermark'       => False,
                'irOverstretch'     => 'True',
                'irRotatetime'      => 10,
                'irTransition'      => 'random',
                'irKenburns'        => False,
                'irBackcolor'       => '000000',
                'irFrontcolor'      => 'FFFFFF',
                'irLightcolor'      => 'CC0000',
                'irScreencolor'     => '000000',

                // CSS Style
                'activateCSS' => TRUE,           // activate the CSS file
                'CSSfile'     => 'nggallery.css', // set default css filename

                // framework settings
                'datamapper_driver' => 'custom_table_datamapper',
                'gallerystorage_driver' => 'ngglegacy_gallery_storage'
            );
        }

        return array(
            'gallerypath' => 'wp-content/blogs.dir/%BLOG_ID%/files/',
            'wpmuCSSfile' => 'nggallery.css'
        );
    }
}

/**
 * Provides persistence for NextGen Settings using WordPress options API
 */
class Mixin_WordPress_NextGen_Settings_Persistance extends Mixin
{
    /**
     * Flushes both local and multisite options from C_NextGen_Settings to the database
     *
     * @return bool
     */
    function save()
	{
		$valid = True;

		// Run validation, if available
		if ($this->object->has_method('validate')) {
			if (!$this->validate()); $valid = False;
		}

		// Save settings
		if ($valid) {
			$valid = update_option(
				$this->object->_get_wordpress_option_name(),
				$this->object->_options
			);

			if ($valid && $this->object->is_multisite()) {
				$valid = update_site_option(
					$this->object->_get_wordpress_option_name(TRUE),
					$this->object->_global_options
				);
			}
		}

		return $valid;
	}

	/**
	 * Fetches the settings from the database; repopulates both local and multisite options
	 */
	function reload($defaults = False)
	{
		// Get options
        if (True !== $defaults)
        {
            $this->object->_options = get_option(
                $this->object->_get_wordpress_option_name(),
                array()
            );

            // Get global options
            $this->object->_global_options = get_site_option(
                $this->object->_get_wordpress_option_name(TRUE),
                array()
            );
        }

		if (True == $defaults || (empty($this->object->_global_options) && empty($this->object->_options))) {
            $this->object->reset(True);
		}
	}

	/**
	 * Returns the name of the WordPress option used to store the settings
     *
	 * @param bool $global optionally, get the name of the option used to store global settings
	 * @return string
	 */
	function _get_wordpress_option_name($global = False)
	{
		// There's actually no distinction in the option name
		return $global ? 'ngg_options' : 'ngg_options';
	}

	/**
	 *  Determines whether multisite mode is activated for WordPress.
     *
     * This method first checks for a global called NGG_MULTISITE that can be set
	 * to TRUE OR FALSE, so that the testing framework can control the env.
	 * If not set, then the test failsback to the is_multisite() function
	 * defined by WordPress
	 */
	function is_multisite()
	{
        $retval = False;

		if (isset($GLOBALS['NGG_MULTISITE']))
        {
			if ($GLOBALS['NGG_MULTISITE'])
            {
                $retval = True;
            }
		} else {
            $retval = is_multisite();
        }

		return $retval;
	}
}

/**
 * Provides the implementation for the NextGen Settings class
 */
class Mixin_NextGen_Settings extends Mixin
{
	/**
	 * Resets NextGEN to it's default settings
     *
     * @param bool $save Whether to immediately call save() when done
     * @return null
	 */
	function reset($save = False)
	{
        $this->object->_options = array();
        foreach (C_NextGen_Settings_Defaults::get_defaults() as $name => $val)
        {
            $this->object->set($name, $val);
        }
        if ($save)
        {
            $this->object->save();
        }
	}

	/**
	 * Gets the value of a setting
     *
	 * @param string $option_name
	 * @return mixed
	 */
	function get($option_name)
	{
		$retval = Null;

        if (isset($this->object->_options[$option_name])) {
            $retval = $this->object->_options[$option_name];
        }

		return $retval;
	}


	/**
	 * Sets a settings option to a particular value
     *
	 * @param string $option_name
	 * @param mixed $value
	 * @return mixed $value
	 */
	function set($option_name, $value)
	{
        $this->object->_options[$option_name] = $value;
		return $value;
	}

    /**
     * Removes a setting from the settings list
     *
     * @param string $option_name
     * @return null
     */
    function del($option_name)
    {
        unset($this->object->_options[$option_name]);
    }

    /**
     * Returns whether a setting exists
     *
     * @param string $option_name
     * @return bool isset()
     */
    function is_set($option_name)
    {
        return isset($this->object->_options[$option_name]);
    }

    /**
     * Returns the current options as an array
     *
     * @return array
     */
    function to_array()
    {
        return $this->object->_options;
    }
}

class C_NextGen_Settings extends C_Component implements ArrayAccess
{
    /** @var array Array of multisite option names */
    public $_global_option_names = array(
        'wpmuCSSfile',
        'gallerypath'
    );

    /** @var Internal multisite options array*/
    public $_global_options;

    /** @var Internal options array */
    public $_options;

    /** @var null Singleton instance */
    public static $_instances = array();

	function define()
	{
		parent::define();

        $this->implement('I_NextGen_Settings');

		// Add persistence layer. Replace if not using WordPress
		$this->add_mixin('Mixin_WordPress_NextGen_Settings_Persistance');

		// Default options API
		$this->add_mixin('Mixin_NextGen_Settings');
	}

	function initialize($context = False)
	{
		parent::initialize($context);
		$this->object->reload();
	}

    function __get($option_name)
    {
        return $this->get($option_name);
    }

    function __set($option_name, $value)
    {
        return $this->set($option_name, $value);
    }

    function __isset($option_name)
    {
        return $this->is_set($option_name);
    }

    function __unset($option_name)
    {
        $this->del($option_name);
    }

    /*
     * Returns whether an option is a multisite option
     *
     * @param string $option_name
     * @return bool in_array()
     */
    function is_global_option($option_name)
    {
        return in_array($option_name, $this->_global_option_names);
    }

    /**
     * Singleton loader
     *
     * @static
     * @param bool $context
     * @return null
     */
    static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_NextGen_Settings($context);
        }
        return self::$_instances[$context];
    }

    /**
     * Used to implement ArrayAccess
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return $this->is_set($offset);
    }

    /**
     * Used to implement ArrayAccess
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Used to implement ArrayAccess
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Used to implement ArrayAccess
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->del($offset);
    }

}
