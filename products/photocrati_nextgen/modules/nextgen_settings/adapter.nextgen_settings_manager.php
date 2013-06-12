<?php

class Mixin_Global_NextGen_Settings extends Mixin
{
	function initialize()
	{
		if (is_multisite()) {
			$defaults = array(
				'gallerypath'	=> 'wp-content/blogs.dir/%BLOG_ID%/files/',
				'wpmuCSSfile'	=> 'nggallery.css',
				'wpmuStyle'		=> TRUE
			);
			foreach ($defaults as $key=>$value) $this->object->set_default($key, $value);
		}
	}

	function save()
	{
		return update_site_option($this->object->_get_option_name(), $this->object->_options);
	}

	function load()
	{
		$this->object->_options = get_site_option(
			$this->object->_get_option_name(),
			array()
		);
	}
}

class Mixin_NextGen_Settings extends Mixin
{
	function initialize()
	{
		// Some of the site-specific settings are based on globals
		$global_settings = $this->get_registry()->get_utility('I_Settings_Manager', 'global');
		$gallerypath = str_replace(
			'%BLOG_ID%',
			get_current_blog_id(),
			$global_settings->get('gallerypath', 'wp-content/gallery/')
		);
		$cssfile		= $global_settings->get('wpmuCSSfile', 'nggallery.css');
		$activateCSS	= $global_settings->get('wpmuStyle', 1);
		unset($global_settings);

		// Set the defaults
		$defaults = array (
			'gallerypath'    => $gallerypath,
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
			'galImgBrowser'     => 0,       // Show ImageBrowser => instead effect
			'galHiddenImg'      => 0,       // For paged galleries we can hide image
			'galAjaxNav'        => 0,       // AJAX Navigation for Shutter effect

			// Thumbnail Effect
			'thumbEffect'  => 'shutter',                           // select effect
			'thumbCode'    => 'class="shutterset_%GALLERY_NAME%"', //

			// Watermark settings
			'wmPos'    => 'botRight',             // Postion
			'wmXpos'   => 5,                      // X Pos
			'wmYpos'   => 5,                      // Y Pos
			'wmType'   => 0,                 // Type : 'image' / 'text'
			'wmPath'   => '',                     // Path to image
			'wmFont'   => 'arial.ttf',            // Font type
			'wmSize'   => 10,                     // Font Size
			'wmText'   => get_option('blogname'), // Text
			'wmColor'  => '000000',               // Font Color
			'wmOpaque' => '100',                  // Font Opaque

			// Image Rotator settings
			'enableIR'          => 0,
			'slideFx'           => 'fade',
			'irURL'             => '',
			'irXHTMLvalid'      => 0,
			'irAudio'           => '',
			'irWidth'           => 600,
			'irHeight'          => 400,
			'irShuffle'         => True,
			'irLinkfromdisplay' => True,
			'irShownavigation'  => 0,
			'irShowicons'       => 0,
			'irWatermark'       => 0,
			'irOverstretch'     => 'True',
			'irRotatetime'      => 10,
			'irTransition'      => 'random',
			'irKenburns'        => 0,
			'irBackcolor'       => '000000',
			'irFrontcolor'      => 'FFFFFF',
			'irLightcolor'      => 'CC0000',
			'irScreencolor'     => '000000',

			// CSS Style
			'activateCSS'		=> $activateCSS,   // activate the CSS file
			'CSSfile'			=> $cssfile, // set default css filename

			// Framework settings
			'datamapper_driver'		=> 'custom_table_datamapper',
			'gallerystorage_driver' => 'ngglegacy_gallery_storage',
			'maximum_entity_count'	=> 500,
            'router_param_slug'     =>  'nggallery',
            'resource_minifier'     => TRUE
		);

		foreach ($defaults as $key=>$value) $this->object->set_default($key, $value);
	}
}

/**
 * Adds NextGEN Gallery specific logic
 */
class A_NextGen_Settings_Manager extends Mixin
{
	function initialize()
	{
		if ($this->object->is_global_context()) $this->object->add_mixin('Mixin_Global_NextGen_Settings');
		else $this->object->add_mixin('Mixin_NextGen_Settings');
	}

	function _get_option_name()
	{
		return 'ngg_options';
	}

	function is_global_context()
	{
		return $this->object->has_context('global');
	}
}
