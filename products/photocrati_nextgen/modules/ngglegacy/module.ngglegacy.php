<?php

/***
	{
		Module: photocrati-nextgen-legacy,
		Depends: { photocrati-base }
	}
 ***/
class M_NggLegacy extends C_Base_Module
{
	var $version     = '1.9.3';
	var $dbversion   = '1.8.0';
	var $manage_page;
	var $add_PHP5_notice = false;

	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen-legacy',
			'NextGEN Legacy',
			'Embeds the original version of NextGEN 1.9.3 by Alex Rabe',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->_options = $this->_get_registry()->get_utility('I_Photocrati_Options');
		$this->_define_constants();
		$this->_load_dependencies();
		$this->_start_rewrite_module();
	}


	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
		// Show NextGEN version in header
        add_action('wp_head', array(&$this, 'nextgen_version') );

		// Sanitize gallery names
		add_filter('ngg_gallery_name', 'sanitize_title');

		// Register custom taxonomies
		$this->_register_taxonomy();

		// Check for the header / footer, parts taken from Matt Martz (http://sivel.net/)
		// TODO: I have no idea what this is supposed to accomplish
    	if ( isset( $_GET['test-head'] ) )
    		add_action( 'wp_head', create_function('', 'echo \'<!--wp_head-->\';'), 99999 );

    	if ( isset( $_GET['test-footer'] ) )
    		add_action( 'wp_footer', create_function('', 'echo \'<!--wp_footer-->\';'), 99999 );

		if (!is_admin()) {
			// Add MRSS to front-end
			// TODO: Define where the plugin will get it's options from
			if (isset($this->_options->useMediaRSS) && $this->_options->useMediaRSS) {
				add_action(
					'wp_head', array('nggMediaRss', 'add_mrss_alternate_link')
				);

				// Add scripts & stylesheets
				add_action('template_redirect', array(&$this, 'load_scripts') );
				add_action('template_redirect', array(&$this, 'load_styles') );
			}
		}

		// Look for XML request, before page is render
		add_action('parse_request',  array(&$this, 'check_request') );
	}


	/**
	 * Defines the constants needed by NextGEN legacy
	 * @global type $wp_version
	 */
	function _define_constants()
	{
		// NextGEN Legacy Constants
		global $wp_version;
		//TODO:SHOULD BE REMOVED LATER
		define('NGGVERSION', $this->version);
		// Minimum required database version
		define('NGG_DBVERSION', $this->dbversion);

		// required for Windows & XAMPP
		define('WINABSPATH', str_replace("\\", "/", ABSPATH) );

		// define URL
		define('NGGFOLDER', basename( dirname(__FILE__) ) );

		define('NGGALLERY_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . NGGFOLDER ) ) );
		define('NGGALLERY_URLPATH', trailingslashit( plugins_url( NGGFOLDER ) ) );

		// look for imagerotator
		// TODO: Define where to get options from. Most likely the
		// C_Photocrati_Options class
		define('NGGALLERY_IREXIST', isset( $this->options['irURL'] ));

		// get value for safe mode
		if ( (gettype( ini_get('safe_mode') ) == 'string') ) {
			// if sever did in in a other way
			if ( ini_get('safe_mode') == 'off' ) define('SAFE_MODE', FALSE);
			else define( 'SAFE_MODE', ini_get('safe_mode') );
		} else
		define( 'SAFE_MODE', ini_get('safe_mode') );

        if ( version_compare($wp_version, '3.2.999', '>') )
            define('IS_WP_3_3', TRUE);

		define('NGGALLERY_DONATORS_URL', 'http://www.nextgen-gallery.com/donators.php');

		// Define capabilities
		$caps = array(
			'PHOTOCRATI_GALLERY_ADD_GALLERY_CAP'		=>
			'NextGEN Add new gallery',
			'PHOTOCRATI_GALLERY_UPLOAD_ZIP_CAP'			=>
			'NextGEN Upload a zip',
			'PHOTOCRATI_GALLERY_IMPORT_FOLDER_CAP'		=>
			'NextGEN Import image folder',
			'PHOTOCRATI_GALLERY_MANAGE_ANY_GALLERY_CAP'	=>
			'NextGEN Upload in all galleries',
			'PHOTOCRATI_GALLERY_UPLOAD_IMAGE_CAP'		=>
			'NextGEN Upload images',
			'PHOTOCRATI_GALLERY_MANAGE_GALLERY_CAP'		=>
			'NextGEN Manage gallery',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_AUTHOR_CAP'=>
			'NextGEN Edit gallery author',
			'PHOTOCRATI_GALLERY_MANAGE_ALBUM_CAP'		=>
			'NextGEN Edit album',
			'PHOTOCRATI_GALLERY_MANAGE_TAGS_CAP'		=>
			'NextGEN Manage tags',
			'PHOTOCRATI_GALLERY_CHANGE_OPTIONS_CAP'		=>
			'NextGEN Change options',
			'PHOTOCRATI_GALLERY_OVERVIEW_CAP'			=>
			'NextGEN Gallery overview',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_OPTIONS_CAP'=>
			'NextGEN Edit gallery options',
			'PHOTOCRATI_GALLERY_MANAGE_ALBUMS_CAP'		=>
			'NextGEN Add/Delete album',
			'PHOTOCRATI_GALLERY_CHANGE_ALBUM_OPTIONS_CAP'=>
			'NextGEN Edit album settings',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_TITLE_CAP'	=>
			'NextGEN Edit gallery title',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_PATH'		=>
			'NextGEN Edit gallery path',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_DESC'		=>
			'NextGEN Edit gallery description',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_PAGE_CAP'=>
			'NextGEN Edit gallery page id',
			'PHOTOCRATI_GALLERY_EDIT_GALLERY_PREVIEW_CAP' =>
			'NextGEN Edit gallery preview pic'




		);
		foreach ($caps as $constant => $value) {
			define($constant, $value);
		}
	}


	/**
	 * Loads NGG legacy dependencies
	 */
	function _load_dependencies()
	{
		// Load global libraries												// average memory usage (in bytes)
		require_once (dirname (__FILE__) . '/lib/core.php');					//  94.840
		require_once (dirname (__FILE__) . '/lib/ngg-db.php');					// 132.400
		require_once (dirname (__FILE__) . '/lib/image.php');					//  59.424
		require_once (dirname (__FILE__) . '/lib/tags.php');				    // 117.136
		require_once (dirname (__FILE__) . '/lib/post-thumbnail.php');			//  n.a.
		require_once (dirname (__FILE__) . '/widgets/widgets.php');				// 298.792
        require_once (dirname (__FILE__) . '/lib/multisite.php');
        require_once (dirname (__FILE__) . '/lib/sitemap.php');

        // Load frontend libraries
        require_once (dirname (__FILE__) . '/lib/navigation.php');		        // 242.016
        require_once (dirname (__FILE__) . '/nggfunctions.php');		        // n.a.
		require_once (dirname (__FILE__) . '/lib/shortcodes.php'); 		        // 92.664

		//Just needed if you access remote to WordPress
		if ( defined('XMLRPC_REQUEST') )
			require_once (dirname (__FILE__) . '/lib/xmlrpc.php');

		// We didn't need all stuff during a AJAX operation
		if ( defined('DOING_AJAX') )
			require_once (dirname (__FILE__) . '/admin/ajax.php');
		else {
			require_once (dirname (__FILE__) . '/lib/meta.php');				// 131.856
			require_once (dirname (__FILE__) . '/lib/media-rss.php');			//  82.768
			require_once (dirname (__FILE__) . '/lib/rewrite.php');				//  71.936
			include_once (dirname (__FILE__) . '/admin/tinymce/tinymce.php'); 	//  22.408

			// Load backend libraries
			if ( is_admin() ) {
				require_once (dirname (__FILE__) . '/admin/admin.php');
				require_once (dirname (__FILE__) . '/admin/media-upload.php');
                if ( defined('IS_WP_3_3') )
				    require_once (dirname (__FILE__) . '/admin/pointer.php');
                $this->nggAdminPanel = new nggAdminPanel();
			}
		}
	}


	/**
	 * Registers a new taxonomy for NextGEN
	 */
	function _register_taxonomy()
	{
		// Register the NextGEN taxonomy
		$args = array(
 	            'label' => __('Picture tag', 'nggallery'),
 	            'template' => __('Picture tag: %2$l.', 'nggallery'),
	            'helps' => __('Separate picture tags with commas.', 'nggallery'),
	            'sort' => true,
 	            'args' => array('orderby' => 'term_order')
				);

		register_taxonomy( 'ngg_tag', 'nggallery', $args );
	}


	/**
	 * Starts the Rewrite Module and adds the NextGen Rewrite Rules
	 * @global nggRewrite $nggRewrite
	 */
	function _start_rewrite_module()
	{
		global $nggRewrite;

		if ( class_exists('nggRewrite') )
			$nggRewrite = new nggRewrite();
	}


	/**
	 * Checks the HTTP request for NextGEN resources
	 * @param WP $wp
	 */
	function check_request( $wp )
	{

    	if ( !array_key_exists('callback', $wp->query_vars) )
    		return;

        if ( $wp->query_vars['callback'] == 'imagerotator') {
            require_once (dirname (__FILE__) . '/xml/imagerotator.php');
            exit();
        }

        if ( $wp->query_vars['callback'] == 'json') {
            require_once (dirname (__FILE__) . '/xml/json.php');
            exit();
        }

        if ( $wp->query_vars['callback'] == 'image') {
            require_once (dirname (__FILE__) . '/nggshow.php');
            exit();
        }

		//TODO:see trac #12400 could be an option for WP3.0
        if ( $wp->query_vars['callback'] == 'ngg-ajax') {
            require_once (dirname (__FILE__) . '/xml/ajax.php');
            exit();
        }
    }


	/**
	 * Loads scripts needed by NGG legacy
	 * @return type
	 */
	function load_scripts()
	{
        // if you don't want that NGG load the scripts, add this constant
        if ( defined('NGG_SKIP_LOAD_SCRIPTS') )
            return;

		// Has a thumbnail effect been set?
		if (isset($this->_options->thumbEffect)) {
			//	activate Thickbox
			if ($this->_options->thumbEffect == 'thickbox') {
				wp_enqueue_script( 'thickbox' );
				// Load the thickbox images after all other scripts
				add_action( 'wp_footer', array(&$this, 'load_thickbox_images'), 11 );

			}

			// activate modified Shutter reloaded if not use the Shutter plugin
			if ( ($this->_options->thumbEffect == "shutter") && !function_exists('srel_makeshutter') ) {
				wp_register_script('shutter', NGGALLERY_URLPATH .'shutter/shutter-reloaded.js', FALSE ,'1.3.3');
				wp_localize_script('shutter', 'shutterSettings', array(
							'msgLoading' => __('L O A D I N G', 'nggallery'),
							'msgClose' => __('Click to Close', 'nggallery'),
							'imageCount' => '1'
				) );
				wp_enqueue_script( 'shutter' );
			}
		}

		// required for the slideshow
		if ( NGGALLERY_IREXIST == TRUE && $this->_options->enableIR && nggGallery::detect_mobile_phone() === FALSE )
			wp_enqueue_script('swfobject', NGGALLERY_URLPATH .'admin/js/swfobject.js', FALSE, '2.2');
        else {
            wp_register_script('jquery-cycle', NGGALLERY_URLPATH .'js/jquery.cycle.all.min.js', array('jquery'), '2.9995');
            wp_enqueue_script('ngg-slideshow', NGGALLERY_URLPATH .'js/ngg.slideshow.min.js', array('jquery-cycle'), '1.06');

        }

		// Load AJAX navigation script, works only with shutter script as we need to add the listener
		if ( $this->_options->galAjaxNav ) {
			if ( ($this->_options->thumbEffect == "shutter") || function_exists('srel_makeshutter') ) {
				wp_enqueue_script ( 'ngg_script', NGGALLERY_URLPATH . 'js/ngg.js', array('jquery'), '2.1');
				wp_localize_script( 'ngg_script', 'ngg_ajax', array('path'		=> NGGALLERY_URLPATH,
                                                                    'callback'  => trailingslashit( home_url() ) . 'index.php?callback=ngg-ajax',
																	'loading'	=> __('loading', 'nggallery'),
				) );
			}
		}

        // If activated, add PicLens/Cooliris javascript to footer
		if ( $this->_options->usePicLens )
            nggMediaRss::add_piclens_javascript();
	}


	/**
	 * Loads styles needed by NGG legacy
	 */
	function load_styles()
	{
		// check first the theme folder for a nggallery.css
		if ( nggGallery::get_theme_css_file() )
			wp_enqueue_style('NextGEN', nggGallery::get_theme_css_file() , false, '1.0.0', 'screen');
		else if ($this->_options->activateCSS)
			wp_enqueue_style('NextGEN', NGGALLERY_URLPATH . 'css/' . $this->_options->CSSfile, false, '1.0.0', 'screen');

		//	activate Thickbox
		if ($this->_options->thumbEffect == 'thickbox')
			wp_enqueue_style( 'thickbox');

		// activate modified Shutter reloaded if not use the Shutter plugin
		if ( ($this->_options->thumbEffect == 'shutter') && !function_exists('srel_makeshutter') )
			wp_enqueue_style('shutter', NGGALLERY_URLPATH .'shutter/shutter-reloaded.css', false, '1.3.4', 'screen');
	}


	/**
	 * Loads thickbox images
	 */
	function load_thickbox_images()
	{
		// TODO: Need to look at this function in general. Appears to be using
		// stuff that should make use of wp_enqueue_script
		// WP core reference relative to the images. Bad idea
		echo "\n" . '<script type="text/javascript">tb_pathToImage = "' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif";tb_closeImage = "' . site_url() . '/wp-includes/js/thickbox/tb-close.png";</script>'. "\n";
	}


	function nextgen_version()
	{
		echo apply_filters('show_nextgen_version', '<!-- <meta name="NextGEN" version="'. $this->version . '" /> -->' . "\n");
	}
}