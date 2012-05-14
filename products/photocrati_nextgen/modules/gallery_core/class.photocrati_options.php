<?php

class Mixin_Photocrati_Options extends Mixin
{
	/**
	 * Gets the default options for the plugin
	 */
	function _set_defaults()
	{
		$this->object->settings = array_merge($this->object->settings, array(

			/************** CORE SETTINGS ************************************/

			// determines what version the plugin was initialized with
			'init_version'				=>	FALSE,

			// DataMapper Driver (factory method)
			'datamapper_driver'			=>	'custom_post_datamapper',

			// Gallery Storage Driver (factory method)
			'gallery_storage_driver'	=>	'wordpress_gallery_storage',

			// Default gallery path for NggLegacy Gallery Storage Driver
			'gallerypath'				=>	(is_multisite() ?
										'wp-content/blogs.dir/%BLOG_ID%/files/':
										'wp-content/gallery/'
										),

			// activate the batch upload functionality
			'swfUpload'					=>	TRUE,

			// use permalinks
			'usePermalinks'				=>	FALSE,

			// the default slug for permalinks
			'permalinkSlug'				=>	'nggallery',

			// default graphics library
			'graphicLibrary'			=>	'gd',

			// Default path to ImageMagick binaries
			'imageMagickDir'			=>	'/usr/local/bin',

			// activate the global Media RSS file
			'useMediaRSS'				=>	FALSE,

			/************** TAGS / CATEGORIES ********************************/

			// append related images
			'activateTags'				=>	FALSE,

			// look for category or tags
			'appendType'				=>	'tags',

			// number of images to show
			'maxImages'					=>	7,

			/************** THUMBNAILS ***************************************/

			// Thumb Width
			'thumbwidth'				=>	100,

			// Thumb height
			'thumbheight'				=>	75,

			// Fix the dimension
			'thumbfix'					=>	TRUE,

			// Thumbnail quality
			'thumbquality'				=>	100,

			/************** IMAGE SETTINGS ***********************************/

			// Image Width
			'imgWidth'					=>	800,

			// Image height
			'imgHeight'					=>	600,

			// Image Quality
			'imgQuality'				=>	85,

			// Create a backup when an image has been modified
			'imgBackup'					=>	TRUE,

			// Resize after upload
			'imgAutoResize'				=>	FALSE,

			/************** GALLERY SETTINGS *********************************/

			// determines whether or not to delete images with galleries
			'deleteImg'					=>	TRUE,

			// activate the PicLens Link for galleries
			'usePicLens'				=>	FALSE,

			// Number of images per page
			'galImages'					=>	'20',

			// Number of galleries per page (in a album)
			'galPagedGalleries'			=>	0,

			// Number of columns for the gallery
			'galColumns'				=>	0,

			// Show slideshow
			'galShowSlide'				=>	TRUE,

			// Text for slideshow
			'galTextSlide'				=>	__(
											  '[Show as slideshow]',
											  PHOTOCRATI_GALLERY_I8N_DOMAIN
											),
			// Text for gallery
			'galTextGallery'			=>	__(
												'[Show picture list]',
												PHOTOCRATI_GALLERY_I8N_DOMAIN
											),
			// Show order
			'galShowOrder'				=>	'gallery',

			// Sort order
			'galSort'					=>	'sortorder',

			// Sort direction
			'galSortDir'				=>	'ASC',

			// use no subpages for gallery
			'galNoPages'				=>	TRUE,

			// Show ImageBrowser, instead effect
			'galImgBrowser'				=>	FALSE,

			// For paged galleries we can hide image
			'galHiddenImg'				=>	FALSE,

			// AJAX Navigation for Shutter effect
			'galAjaxNav'				=>	FALSE,

			/************** THUMBNAIL EFFECT *********************************/

			// select effect
			'thumbEffect'				=>	'shutter',
			'thumbCode'					=>	'class="shutterset_%GALLERY_NAME%"',

			/************** WATERMARK SETTINGS *******************************/

			// Postion
			'wmPos'						=>	'botRight',

			// X Pos
			'wmXpos'					=>	5,

			// Y Pos
			'wmYpos'					=>	5,

			// Type : 'image' / 'text'
			'wmType'					=>	'text',

			// Path to image
			'wmPath'					=>	'',

			// Font type
			'wmFont'					=>	'arial.ttf',

			// Font size
			'wmSize'					=>	10,

			// Text
			'wmText'					=>	get_option('blogname'),

			// Font color
			'wmColor'					=>	'000000',

			// Font opaque
			'wmOpaque'					=>	'100',

			/************** IMAGE ROTATOR SETTINGS ***************************/

			// Enable image rotator
			'enableIR'					=>	FALSE,

			// Slide Effect
			'slideFx'					=>	'fade',

			// Image Rotator URL
			'irURL'						=>	'',

			// TODO: Not sure what this does
			'irXHTMLvalid'				=>	FALSE,

			// TODO: Not sure what this does
			'irAudio'					=>	'',

			// Width of the image rotator
			'irWidth'					=>	320,

			// Height of the image rotator
			'irHeight'					=>	240,

			// Shuffle images
			'irShuffle'					=>	FALSE,

			// TODO: Not sure what this does
			'irLinkfromdisplay'			=>	TRUE,

			// Show navigation buttons
			'irShownavigation'			=>	FALSE,

			// Show icons
			'irShowicons'				=>	FALSE,

			// Show watermark
			'irWatermark'				=>	FALSE,

			// Stretch images
			'irOverstretch'				=>	'true',

			// Image rotation time (in seconds)
			'irRotatetime'				=>	10,

			// Random type
			'irTransition'				=>	'random',

			// Use Kenburns Effect
			'irKenburns'				=>	FALSE,

			// Background color of the image rotator
			'irBackcolor'				=>	'000000',

			// Foreground color
			'irFrontcolor'				=>	'FFFFFF',

			// TODO: Not sure what this does
			'irLightcolor'				=>	'CC0000',

			// TODO: Not sure what this does
			'irScreencolor'				=>	'000000',

			// Activate custom css
			'activateCSS'				=>	TRUE,

			// Name of the custom css file
			'CSSfile'					=>	'nggallery.css',
		));
	}
}

class C_Photocrati_Options extends C_Base_Component_Config
{
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Photocrati_Options');
		$this->implement('I_Photocrati_Options');
	}
}