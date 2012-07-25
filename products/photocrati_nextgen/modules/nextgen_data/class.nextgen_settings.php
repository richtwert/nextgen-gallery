<?php

/**
 * Provides persistance for NextGen Settings using WordPress options API
 */
class Mixin_WordPress_NextGen_Settings_Persistance extends Mixin
{
	function save()
	{
		$valid = TRUE;

		// Run validation, if available
		if ($this->object->has_method('validate')) {
			if (!$this->validate()); $valid = FALSE;
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
	 * Fetches the settings from the database
	 */
	function reload()
	{
		// Get options
		$this->object->_options = get_option(
			$this->object->_get_wordpress_option_name(),
			array()
		);

		// Get global options
		$this->object->_global_options = get_site_option(
			$this->object->_get_wordpress_option_name(TRUE),
			array()
		);

		if (empty($this->object->_global_options) && empty($this->object->_options)) {
			$this->object->reset(TRUE);
		}
	}


	/**
	 * Returns the name of the WordPress option used to store the settings
	 * @param bool $global optionally, get the name of the option used to store global settings
	 * @return string
	 */
	function _get_wordpress_option_name($global=FALSE)
	{
		// There's actually no distinction in the option name
		return $global ? 'ngg_options' : 'ngg_options';
	}


	/**
	 *  Determines whether multisite mode is activated for WordPress. This
	 *  method first checks for a global called NGG_MULTISITE that can be set
	 *  to TRUE OR FALSE, so that the testing framework can control the env.
	 *  If not set, then the test failsback to the is_multisite() function
	 *  defined by WordPress
	 */
	function is_multisite()
	{
		$retval = FALSE;

		if (isset($GLOBALS['NGG_MULTISITE'])) {
			if ($GLOBALS['NGG_MULTISITE']) $retval = TRUE;
		}
		else $retval = is_multisite();

		return $retval;
	}
}


/**
 *  Hook triggered after a global option has been set
 */
class Hook_NextGen_Settings_WordPress_MU_Overrides extends Hook
{
	function _apply_multisite_overrides($option_name, $global=FALSE)
	{
		// If in a multisite environment and a global option is being set...
		if ($this->object->is_multisite() && ($this->object->is_global_option($option_name) OR $global)) {

			switch ($option_name) {
				case 'CSSFile':
					$this->call_anchor(
						$option_name,
						$this->object->get_global($option_name),
						FALSE
					);
					break;
				case 'gallerypath':
					$blog_id = get_current_blog_id();
					$global_value = $this->object->get_global($option_name);
					$this->call_anchor(
						$option_name,
						str_replace('%BLOG_ID%', $blog_id, $global_value),
						FALSE
					);
					break;
			}

			return $this->object->get_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
			);
		}
	}
}

/**
 * Provides the implementation for the NextGen Settings class
 */
class Mixin_NextGen_Settings extends Mixin
{
	/**
	 * Determines whether an option name is global or blog specific
	 * @param string $option_name
	 * @return bool
	 */
	function is_global_option($option_name)
	{
		return in_array($option_name, $this->object->_global_option_names);
	}

	/**
	 * Resets NextGEN to it's default settings
	 */
	function reset($save=FALSE)
	{
		// Reset other options
		$this->object->set('gallerypath',		'wp-content/gallery/');
		$this->object->set('deleteImg',			TRUE);							// delete Images
		$this->object->set('swfUpload',			TRUE);							// activate the batch upload
		$this->object->set('usePermalinks',		FALSE);							// use permalinks for parameters
		$this->object->set('permalinkSlug',		'nggallery');                  // the default slug for permalinks
		$this->object->set('graphicLibrary',	'gd');							// default graphic library
		$this->object->set('imageMagickDir',	'/usr/local/bin/');			// default path to ImageMagick
		$this->object->set('useMediaRSS',		FALSE);						// activate the global Media RSS file
		$this->object->set('usePicLens',		FALSE);						// activate the PicLens Link for galleries

		// Tags / categories
		$this->object->set('activateTags',		FALSE);						// append related images
		$this->object->set('appendType',		'tags');						// look for category or tags
		$this->object->set('maxImages',			7);  							// number of images toshow

		// Thumbnail Settings
		$this->object->set('thumbwidth',		100);  						// Thumb Width
		$this->object->set('thumbheight',		75);  							// Thumb height
		$this->object->set('thumbfix',			TRUE);							// Fix the dimension
		$this->object->set('thumbquality',		100);  						// Thumb Quality

		// Image Settings
		$this->object->set('imgWidth',			800);  						// Image Width
		$this->object->set('imgHeight',			600);  						// Image height
		$this->object->set('imgQuality',		85);							// Image Quality
		$this->object->set('imgBackup',			TRUE);							// Create a backup
		$this->object->set('imgAutoResize',		FALSE);						// Resize after upload

		// Gallery Settings
		$this->object->set('galImages',			'20');		  					// Number of images per page
		$this->object->set('galPagedGalleries',	0);		  					// Number of galleries per page (in a album)
		$this->object->set('galColumns',		0);							// Number of columns for the gallery
		$this->object->set('galShowSlide',		TRUE);							// Show slideshow
		$this->object->set('galTextSlide',		__('[Show as slideshow]','nggallery')); // Text for slideshow
		$this->object->set('galTextGallery',	__('[Show picture list]','nggallery')); // Text for gallery
		$this->object->set('galShowOrder',		'gallery');					// Show order
		$this->object->set('galSort',			'sortorder');					// Sort order
		$this->object->set('galSortDir',		'ASC');						// Sort direction
		$this->object->set('galNoPages',		TRUE);							// use no subpages for gallery
		$this->object->set('galImgBrowser',		FALSE);						// Show ImageBrowser, instead effect
		$this->object->set('galHiddenImg',		FALSE);						// For paged galleries we can hide image
		$this->object->set('galAjaxNav',		FALSE);						// AJAX Navigation for Shutter effect

		// Thumbnail Effect
		$this->object->set('thumbEffect',		'shutter');  					// select effect
		$this->object->set('thumbCode',			'class="shutterset_%GALLERY_NAME%"');

		// Watermark settings
		$this->object->set('wmPos',				'botRight');					// Postion
		$this->object->set('wmXpos',			5);  							// X Pos
		$this->object->set('wmYpos',			5);  							// Y Pos
		$this->object->set('wmType',			'text');  						// Type : 'image' / 'text'
		$this->object->set('wmPath',			'');  							// Path to image
		$this->object->set('wmFont',			'arial.ttf');  				// Font type
		$this->object->set('wmSize',			10);  							// Font Size
		$this->object->set('wmText',			get_option('blogname'));		// Text
		$this->object->set('wmColor',			'000000');  					// Font Color
		$this->object->set('wmOpaque',			'100');  						// Font Opaque

		// Image Rotator settings
		$this->object->set('enableIR',			FALSE);
		$this->object->set('slideFx',			'fade');
		$this->object->set('irURL',				'');
		$this->object->set('irXHTMLvalid',		FALSE);
		$this->object->set('irAudio',			'');
		$this->object->set('irWidth',			320);
		$this->object->set('irHeight',			240);
		$this->object->set('irShuffle',			TRUE);
		$this->object->set('irLinkfromdisplay', TRUE);
		$this->object->set('irShownavigation',  FALSE);
		$this->object->set('irShowicons',		FALSE);
		$this->object->set('irWatermark',		FALSE);
		$this->object->set('irOverstretch',		'true');
		$this->object->set('irRotatetime',		10);
		$this->object->set('irTransition',		'random');
		$this->object->set('irKenburns',		FALSE);
		$this->object->set('irBackcolor',		'000000');
		$this->object->set('irFrontcolor',		'FFFFFF');
		$this->object->set('irLightcolor',		'CC0000');
		$this->object->set('irScreencolor',		'000000');

		// CSS Style
		$this->object->set('activateCSS',		TRUE);							// activate the CSS file
		$this->object->set('CSSfile',			'nggallery.css');  			// set default css filename

		// Reset globals
		$this->object->set('gallerypath', 'wp-content/blogs.dir/%BLOG_ID%/files/', TRUE);
		$this->object->set('wpmuCSSfile', 'nggallery.css', TRUE);

		if ($save) $this->object->save();
	}

	/**
	 * Reloads the settings from the database. This method is expected to be
	 * overwritten / replaced, as currently it does nothing
	 *
	 */
	function reload()
	{
		throw new NotImplementException();
	}


	/**
	 * Returns the value of a global option
	 * @param string $option_name
	 * @return mixed|NULL
	 */
	function get_global($option_name)
	{
		return $this->object->get($option_name, TRUE);
	}

	/**
	 * Gets the value of a setting
	 * @param string $option_name
	 * @param bool $global
	 * @return null|mixed
	 */
	function get($option_name, $global=FALSE)
	{
		$retval = NULL;

		// Is this a global setting?
		if ($global OR $this->object->is_global_option($option_name)) {
			if (isset($this->object->_global_options[$option_name])) {
				$retval = $this->object->_global_options[$option_name];
			}
		}

		// This is NOT a global setting
		else {
			if (isset($this->object->_options[$option_name])) {
				$retval = $this->object->_options[$option_name];
			}
		}

		return $retval;
	}


	/**
	 * Sets a settings option to a particular value
	 * @param string $option_name
	 * @param mixed $value
	 * @param bool $global is the setting global?
	 * @return mixed|FALSE
	 */
	function set($option_name, $value, $global=FALSE)
	{
		$retval = FALSE;

		// Global setting?
		if ($global OR $this->object->is_global_option($option_name)) {
			$this->object->_global_options[$option_name] = $value;
			$retval = $value;
		}

		// Standard (non-GLOBAL) setting...
		else {
			$this->object->_options[$option_name] = $value;
			$retval = $value;
		}

		return $retval;
	}
}

/**
 *  A singleton class providing access to NextGEN settings.
 */
class C_NextGen_Settings extends C_Component
{
	var $_global_option_names = array('wpmuCSSfile');
	var $_global_options;
	var $_options;
	static $_instance = NULL;

	/**
	 * Defines the object
	 */
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_NextGen_Settings');

		// Add persistence layer. Replace if not using WordPress
		$this->add_mixin('Mixin_WordPress_NextGen_Settings_Persistance');

		// Add hook for WordPress substitutions. For instance, when the %BLOG_ID%
		// placeholder is used
		$this->add_post_hook(
			'set',
			'WordPress Multisite Overrides',
			'Hook_NextGen_Settings_WordPress_MU_Overrides',
			'_apply_multisite_overrides'
		);
	}


	/**
	 * Initializes the instance
	 * @param type $context
	 */
	function initialize($context=FALSE)
	{
		parent::initialize($context);
		$this->reload();
	}

	/**
	 * Gets the value of a particular setting
	 * @param string $option_name
	 * @return mixed|NULL
	 */
	function __get($option_name)
	{
		return $this->get($option_name);
	}

	/**
	 * Sets a setting option to a particular value
	 * @param string $option_name
	 * @param mixed $value
	 * @return mixed|FALSE
	 */
	function __set($option_name, $value)
	{
		return $this->set($option_name, $value);
	}

	/**
	 * Determines if a setting has been set
	 * @param string $property
	 * @return bool
	 */
	function __isset($property)
	{
		if (isset($this->_options[$property]) OR isset($this->_global_options[$property]))
			return TRUE;
		else
			return FALSE;
	}


	/**
	 * Gets the singleton instance to manage NextGEN settings
	 * @param mixed $context
	 * @return C_NextGen_Settings
	 */
	static function get_instance($context=FALSE)
	{
		if (is_null(self::$_instance))
			self::$_instance = new C_NextGen_Settings($context);

		return self::$_instance;
	}
}

?>
