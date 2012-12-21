<?php
/*
Plugin Name: NextGEN Gallery
Plugin URI: http://www.nextgen-gallery.com/
Description: A NextGENeration Photo Gallery for WordPress
Author: Photocrati
Author URI: http://www.photocrati.com/
Version: 1.9.9

Copyright (c) 2007-2011 by Alex Rabe & NextGEN DEV-Team
Copyright (c) 2012 Photocrati Media

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Indicates that a clean exit occured. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
	class E_Clean_Exit extends RuntimeException
	{

	}
}


/**
 * Loads the NextGEN plugin
 */
if (!class_exists('nggLoader')) {
	class nggLoader {

		var $version     = '1.9.9';
		var $dbversion   = '1.8.1';
		var $minimum_WP  = '3.4';
		var $donators    = 'http://www.nextgen-gallery.com/donators.php';
		var $options     = '';
		var $manage_page;
		var $add_PHP5_notice = false;
		var $plugin_name = '';
		var $rethrow = FALSE;

		function nggLoader() {

			// Stop the plugin if we missed the requirements
			if ( ( !$this->required_version() ) || ( !$this->check_memory_limit() ) )
				return;

			// Set error handler
//			set_exception_handler(array(&$this, 'exception_handler'));

			// Determine plugin basename based on whether NGG is being used in
			// it's legacy form, or as a Photocrati Gallery
			if (defined('NEXTGEN_GALLERY_PLUGIN_BASENAME')) $this->plugin_name = NEXTGEN_GALLERY_PLUGIN_BASENAME;
			else $this->plugin_name = basename(dirname(__FILE__)).'/'.basename(__FILE__);

			// Get some constants first
			$this->load_options();
			$this->define_constant();
			$this->define_tables();
			$this->load_dependencies();
			$this->start_rewrite_module();

			// Init options & tables during activation & deregister init option
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );

			// Register a uninstall hook to remove all tables & option automatic
			// register_uninstall_hook( $this->plugin_name, array(&$this, 'uninstall') );

			// Start this plugin once all other plugins are fully loaded
			add_action( 'plugins_loaded', array(&$this, 'start_plugin') );

			// Register_taxonomy must be used during the init
			add_action( 'init', array(&$this, 'register_taxonomy') );
			add_action( 'wpmu_new_blog', array(&$this, 'multisite_new_blog'), 10, 6);

			// Add a message for PHP4 Users, can disable the update message later on
			if (version_compare(PHP_VERSION, '5.0.0', '<'))
				add_filter('transient_update_plugins', array(&$this, 'disable_upgrade'));

			//Add some links on the plugin page
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);

			// Check for the header / footer
			add_action( 'init', array(&$this, 'test_head_footer_init' ) );

			// Show NextGEN version in header
			add_action('wp_head', array('nggGallery', 'nextgen_version') );

			// Handle upload requests
			add_action('init', array(&$this, 'handle_upload_request'));
		}

		function start_plugin() {

			global $nggRewrite;

			// Load the language file
			$this->load_textdomain();

			// All credits to the tranlator
			$this->translator  = '<p class="hint">'. __('<strong>Translation by : </strong><a target="_blank" href="http://alexrabe.de/wordpress-plugins/nextgen-gallery/languages/">See here</a>', 'nggallery') . '</p>';
			$this->translator .= '<p class="hint">'. __('<strong>This translation is not yet updated for Version 1.9.0</strong>. If you would like to help with translation, download the current po from the plugin folder and read <a href="http://alexrabe.de/wordpress-plugins/wordtube/translation-of-plugins/">here</a> how you can translate the plugin.', 'nggallery') . '</p>';

			// Content Filters
			add_filter('ngg_gallery_name', 'sanitize_title');

			// Check if we are in the admin area
			if ( is_admin() ) {

				// Pass the init check or show a message
				if (get_option( 'ngg_init_check' ) != false )
					add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>' . get_option( "ngg_init_check" ) . '</strong></p></div>\';') );

			} else {

				// Add MRSS to wp_head
				if ( $this->options['useMediaRSS'] )
					add_action('wp_head', array('nggMediaRss', 'add_mrss_alternate_link'));

				// Look for XML request, before page is render
				add_action('parse_request',  array(&$this, 'check_request') );

				// Add the script and style files
				add_action('wp_enqueue_scripts', array(&$this, 'load_scripts') );
				add_action('wp_enqueue_scripts', array(&$this, 'load_styles') );

			}
		}

		function check_request( $wp ) {

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

		function required_version() {

			global $wp_version;

			// Check for WP version installation
			$wp_ok  =  version_compare($wp_version, $this->minimum_WP, '>=');

			if ( ($wp_ok == FALSE) ) {
				add_action(
					'admin_notices',
					create_function(
						'',
						'global $ngg; printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, NextGEN Gallery works only under WordPress %s or higher\', "nggallery" ) . \'</strong></p></div>\', $ngg->minimum_WP );'
					)
				);
				return false;
			}

			return true;

		}

		function check_memory_limit() {

			// get the real memory limit before some increase it
			$this->memory_limit = ini_get('memory_limit');

			// PHP docs : Note that to have no memory limit, set this directive to -1.
			if ($this->memory_limit == -1 ) return true;

			// Yes, we reached Gigabyte limits, so check if it's a megabyte limit
			if (strtolower( substr($this->memory_limit, -1) ) == 'm') {

				$this->memory_limit = (int) substr( $this->memory_limit, 0, -1);

				//This works only with enough memory, 16MB is silly, wordpress requires already 16MB :-)
				if ( ($this->memory_limit != 0) && ($this->memory_limit < 16 ) ) {
					add_action(
						'admin_notices',
						create_function(
							'',
							'echo \'<div id="message" class="error"><p><strong>' . __('Sorry, NextGEN Gallery works only with a Memory Limit of 16 MB or higher', 'nggallery') . '</strong></p></div>\';'
						)
					);
					return false;
				}
			}

			return true;

		}

		function define_tables() {
			global $wpdb;

			// add database pointer
			$wpdb->nggpictures					= $wpdb->prefix . 'ngg_pictures';
			$wpdb->nggallery					= $wpdb->prefix . 'ngg_gallery';
			$wpdb->nggalbum						= $wpdb->prefix . 'ngg_album';

		}

		function register_taxonomy() {
			global $wp_rewrite;

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

		function define_constant() {

			global $wp_version;

			//TODO:SHOULD BE REMOVED LATER
			define('NGGVERSION', $this->version);
			// Minimum required database version
			define('NGG_DBVERSION', $this->dbversion);

			// required for Windows & XAMPP
			define('WINABSPATH', str_replace("\\", "/", ABSPATH) );

			// define URL
			define('NGGFOLDER', dirname( $this->plugin_name ) );

			define(
				'NGGALLERY_ABSPATH',
				defined('NEXTGEN_GALLERY_NGGLEGACY_MOD_DIR') ?
					trailingslashit(NEXTGEN_GALLERY_NGGLEGACY_MOD_DIR) :
					trailingslashit(dirname(__FILE__))
			);

			define(
				'NGGALLERY_URLPATH',
				defined('NEXTGEN_GALLERY_NGGLEGACY_MOD_URL') ?
					trailingslashit(NEXTGEN_GALLERY_NGGLEGACY_MOD_URL) :
					trailingslashit( plugins_url( NGGFOLDER ) )
			);

			// look for imagerotator
			define('NGGALLERY_IREXIST', !empty( $this->options['irURL'] ));

			// get value for safe mode
			if ( (gettype( ini_get('safe_mode') ) == 'string') ) {
				// if sever did in in a other way
				if ( ini_get('safe_mode') == 'off' ) define('SAFE_MODE', FALSE);
				else define( 'SAFE_MODE', ini_get('safe_mode') );
			} else
			define( 'SAFE_MODE', ini_get('safe_mode') );

			if ( version_compare($wp_version, '3.2.999', '>') )
				define('IS_WP_3_3', TRUE);

		}

		function load_dependencies() {

			// Load global libraries												// average memory usage (in bytes)
			require_once (dirname (__FILE__) . '/lib/core.php');					//  94.840
			require_once (dirname (__FILE__) . '/lib/class.ngg_serializable.php');					//  94.840
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

				// Load backend libraries
				if ( is_admin() ) {
					require_once (dirname (__FILE__) . '/admin/admin.php');
					require_once (dirname (__FILE__) . '/admin/media-upload.php');
					$this->nggAdminPanel = new nggAdminPanel();
				}
			}
		}

		function load_textdomain() {

			load_plugin_textdomain('nggallery', false, NGGFOLDER . '/lang');

		}

		function load_scripts() {

			// if you don't want that NGG load the scripts, add this constant
			if ( defined('NGG_SKIP_LOAD_SCRIPTS') )
				return;

			// required for the slideshow
			if ( NGGALLERY_IREXIST == true && $this->options['enableIR'] == '1' && nggGallery::detect_mobile_phone() === false )
				wp_enqueue_script('swfobject');
			else {
				wp_register_script('jquery-cycle', NGGALLERY_URLPATH .'js/jquery.cycle.all.min.js', array('jquery'), '2.9995');
				wp_enqueue_script('ngg-slideshow', NGGALLERY_URLPATH .'js/ngg.slideshow.min.js', array('jquery-cycle'), '1.06');

			}

			// Load AJAX navigation script, works only with shutter script as we need to add the listener
			if ( $this->options['galAjaxNav'] ) {
				if ( ($this->options['thumbEffect'] == "shutter") || function_exists('srel_makeshutter') ) {
					wp_enqueue_script ( 'ngg_script', NGGALLERY_URLPATH . 'js/ngg.js', array('jquery'), '2.1');
					wp_localize_script( 'ngg_script', 'ngg_ajax', array('path'		=> NGGALLERY_URLPATH,
																		'callback'  => trailingslashit( home_url() ) . 'index.php?callback=ngg-ajax',
																		'loading'	=> __('loading', 'nggallery'),
					) );
				}
			}
		}

		function load_thickbox_images() {
			// WP core reference relative to the images. Bad idea
			echo "\n" . '<script type="text/javascript">tb_pathToImage = "' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif";tb_closeImage = "' . site_url() . '/wp-includes/js/thickbox/tb-close.png";</script>'. "\n";
		}

		function load_styles() {

			// check first the theme folder for a nggallery.css
			if ( nggGallery::get_theme_css_file() )
				wp_enqueue_style('NextGEN', nggGallery::get_theme_css_file() , false, '1.0.0', 'screen');
			else if ($this->options['activateCSS'])
				wp_enqueue_style('NextGEN', NGGALLERY_URLPATH . 'css/' . $this->options['CSSfile'], false, '1.0.0', 'screen');

			//	activate Thickbox
			if ($this->options['thumbEffect'] == 'thickbox')
				wp_enqueue_style( 'thickbox');

			// activate modified Shutter reloaded if not use the Shutter plugin
			if ( ($this->options['thumbEffect'] == 'shutter') && !function_exists('srel_makeshutter') )
				wp_enqueue_style('shutter', NGGALLERY_URLPATH .'shutter/shutter-reloaded.css', false, '1.3.4', 'screen');

		}

		function load_options() {
			// Load the options
			$this->options = get_option('ngg_options');
		}

		// Add rewrite rules
		function start_rewrite_module() {
			// global $nggRewrite;
			// if (class_exists('nggRewrite'))
			//	$nggRewrite = new nggRewrite();
		}

		// THX to Shiba for the code
		// See: http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site
		function multisite_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			global $wpdb;

			include_once (dirname (__FILE__) . '/admin/install.php');

			if (is_plugin_active_for_network( $this->plugin_name )) {
				$current_blog = $wpdb->blogid;
				switch_to_blog($blog_id);
				nggallery_install();
				switch_to_blog($current_blog);
			}
		}

		/**
		 * Removes all transients created by NextGEN. Called during activation
		 * and deactivation routines
		 */
		static function remove_transients()
		{
			global $wpdb, $_wp_using_ext_object_cache;

			// Fetch all transients
			$query = "
				SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$transient_names = $wpdb->get_col($query);;

			// Delete all transients in the database
			$query = "
				DELETE FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$wpdb->query($query);

			// If using an external caching mechanism, delete the cached items
			if ($_wp_using_ext_object_cache) {
				foreach ($transient_names as $transient) {
					wp_cache_delete($transient, 'transient');
					wp_cache_delete(substr($transient, 11), 'transient');
				}
			}
		}

		function activate() {
			global $wpdb;
			//Starting from version 1.8.0 it's works only with PHP5.2
			if (version_compare(PHP_VERSION, '5.2.0', '<')) {
					deactivate_plugins($this->plugin_name); // Deactivate ourself
					wp_die("Sorry, but you can't run this plugin, it requires PHP 5.2 or higher.");
					return;
			}

			// Clean up transients
			self::remove_transients();

			include_once (dirname (__FILE__) . '/admin/install.php');

			if (is_multisite()) {
				$network=isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:"";
				$activate=isset($_GET['action'])?$_GET['action']:"";
				$isNetwork=($network=='/wp-admin/network/plugins.php')?true:false;
				$isActivation=($activate=='deactivate')?false:true;

				if ($isNetwork and $isActivation){
					$old_blog = $wpdb->blogid;
					$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs", NULL));
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						nggallery_install();
					}
					switch_to_blog($old_blog);
					return;
				}
			}

			// check for tables
			nggallery_install();
			// remove the update message
			delete_option( 'ngg_update_exists' );

		}

		function deactivate() {

			// remove & reset the init check option
			delete_option( 'ngg_init_check' );
			delete_option( 'ngg_update_exists' );

			// Clean up transients
			self::remove_transients();
		}

		function uninstall() {
			// Clean up transients
			self::remove_transients();

			include_once (dirname (__FILE__) . '/admin/install.php');
			nggallery_uninstall();
		}

		function disable_upgrade($option){

			// PHP5.2 is required for NGG V1.4.0
			if ( version_compare($option->response[ $this->plugin_name ]->new_version, '1.4.0', '>=') )
				return $option;

			if( isset($option->response[ $this->plugin_name ]) ){
				//Clear it''s download link
				$option->response[ $this->plugin_name ]->package = '';

				//Add a notice message
				if ($this->add_PHP5_notice == false){
					add_action( "in_plugin_update_message-$this->plugin_name", create_function('', 'echo \'<br /><span style="color:red">Please update to PHP5.2 as soon as possible, the plugin is not tested under PHP4 anymore</span>\';') );
					$this->add_PHP5_notice = true;
				}
			}
			return $option;
		}

		// Add links to Plugins page
		function add_plugin_links($links, $file) {

			if ( $file == $this->plugin_name ) {
				$plugin_name = plugin_basename(NGGALLERY_ABSPATH);
				$links[] = "<a href='admin.php?page={$plugin_name}'>" . __('Overview', 'nggallery') . '</a>';
				$links[] = '<a href="http://wordpress.org/tags/nextgen-gallery?forum_id=10">' . __('Get help', 'nggallery') . '</a>';
				$links[] = '<a href="https://bitbucket.org/photocrati/nextgen-gallery">' . __('Contribute', 'nggallery') . '</a>';
			}
			return $links;
		}

		// Check for the header / footer, parts taken from Matt Martz (http://sivel.net/)
		function test_head_footer_init() {

			// If test-head query var exists hook into wp_head
			if ( isset( $_GET['test-head'] ) )
				add_action( 'wp_head', create_function('', 'echo \'<!--wp_head-->\';'), 99999 );

			// If test-footer query var exists hook into wp_footer
			if ( isset( $_GET['test-footer'] ) )
				add_action( 'wp_footer', create_function('', 'echo \'<!--wp_footer-->\';'), 99999 );
		}

		/**
		* Handles upload requests
		*/
		function handle_upload_request()
		{
			if (isset($_GET['nggupload'])) {
				require_once(implode(DIRECTORY_SEPARATOR, array(
					NGGALLERY_ABSPATH,
					'admin',
					'upload.php'
				)));
				throw new E_Clean_Exit();
			}
		}

		/**
		* Handles clean exits gracefully. Re-raises anything else
		* @param Exception $ex
		*/
		function exception_handler($ex)
		{
			if (get_class($ex) != 'E_Clean_Exit') $this->rethrow = $ex;
		}

		function __destruct()
		{
			if ($this->rethrow) throw $this->rethrow;
		}
	}

	// Let's start the holy plugin
	global $ngg;
	$ngg = new nggLoader();
}
