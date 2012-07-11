<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Plugin Name: NextGEN by Photocrati
 * Description: Providing you the best gallery management for WordPress
 * Version: 2.0
 * Plugin URI: http://www.photocrati.com
 * Author URI: http://www.photocrati.com
 */

class C_NextGEN_Bootstrap
{
	private $_minimum_wordpress_version  = '3.2';
	private $_minimum_memory_limit = '16';

	function __construct()
	{
		// Boostrap
		if ($this->are_requirements_met()) {
			$this->_define_constants();
			$this->_register_hooks();

			// Include pope framework
			require_once(path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, implode(
				DIRECTORY_SEPARATOR, array('pope','lib','autoload.php')
			)));

			// Include some extra helpers
			require_once(path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'wordpress_helpers.php'));

			// Load embedded products. Each product is expected any
			// modules required
			$registry = C_Component_Registry::get_instance();
			$registry->add_module_path(PHOTOCRATI_GALLERY_PRODUCT_DIR, true, false);
			$registry->load_module('photocrati-nextgen');

			// Initializes all loaded modules
			$registry->initialize_all_modules();
		}
		else add_action('admin_notices', 'render_requirements_not_met');
	}


	/**
	 * Registers hooks for the WordPress framework necessary for instantiating
	 * the plugin
	 */
	function _register_hooks()
	{
		// Load text domain
		load_plugin_textdomain(
			PHOTOCRATI_GALLERY_I8N_DOMAIN,
			false,
			$this->directory_path('lang')
		);

		register_activation_hook(__FILE__, array(&$this, 'activate'));

		// Register our test suite
		add_filter('simpletest_suites', array(&$this, 'add_testsuite'));
	}

	/**
	 * When the plugin is activated, we ensure that we have what's necessary
	 * for the plugin to run, which are roles installed
	 */
	function activate()
	{
		if (!current_user_can('activate_plugins')) {

			// Grant NextGEN capabilities for Administator role
			$role = get_role('administrator');
			$caps = array(
				PHOTOCRATI_GALLERY_OVERVIEW_CAP,
				'NextGEN Use TinyMCE',
				PHOTOCRATI_GALLERY_UPLOAD_IMAGE_CAP,
				PHOTOCRATI_GALLERY_MANAGE_GALLERY_CAP,
				PHOTOCRATI_GALLERY_MANAGE_TAGS_CAP,
				'NextGEN Manage others gallery',
				PHOTOCRATI_GALLERY_MANAGE_ALBUM_CAP,
				'NextGEN Change style',
				PHOTOCRATI_GALLERY_CHANGE_OPTIONS_CAP
			);
			foreach ($caps as $cap) $role->add_cap($cap);
		}
	}


	/**
	 * Defines necessary plugins for the plugin to load correctly
	 */
	function _define_constants()
	{
		// NextGEN by Photocrati Constants
		define('PHOTOCRATI_GALLERY_PLUGIN', basename($this->directory_path()));
		define('PHOTOCRATI_GALLERY_PLUGIN_DIR', $this->directory_path());
		define('PHOTOCRATI_GALLERY_PLUGIN_URL', $this->path_uri());
		define('PHOTOCRATI_GALLERY_I8N_DOMAIN', 'nggallery');
		define('PHOTOCRATI_GALLERY_TESTS_DIR', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'tests'));
		define('PHOTOCRATI_GALLERY_PRODUCT_DIR', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'products'));
		define('PHOTOCRATI_GALLERY_PRODUCT_URL', path_join(PHOTOCRATI_GALLERY_PLUGIN_URL, 'products'));
		define('PHOTOCRATI_GALLERY_MODULE_DIR', path_join(PHOTOCRATI_GALLERY_PRODUCT_DIR, 'photocrati_nextgen/modules'));
		define('PHOTOCRATI_GALLERY_MODULE_URL', path_join(PHOTOCRATI_GALLERY_PRODUCT_URL, 'photocrati_nextgen/modules'));
		define('PHOTOCRATI_GALLERY_PLUGIN_CLASS', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'module.photocrati_gallery_plugin.php'));
		define('PHOTOCRATI_GALLERY_PLUGIN_STARTED_AT', microtime());
		define('PHOTOCRATI_GALLERY_OPTION_PREFIX', 'nggallery');
		define('PHOTOCRATI_GALLERY_PLUGIN_VERSION', '1.9.5');
	}


	/**
	 * Defines the NextGEN Test Suite
	 * @param array $suites
	 * @return array
	 */
	function add_testsuite($suites=array())
	{
		// Define Test Directory
		$tests_dir = PHOTOCRATI_GALLERY_TESTS_DIR;

		if (file_exists($tests_dir)) {
			// Include mock objects
			require_once(path_join($tests_dir, 'mocks.php'));

			// Define the NextGEN Test Suite
			$suites['nextgen'] = array(
	//			path_join($tests_dir, 'datamapper'),
	//			path_join($tests_dir, 'gallery_storage'),
				path_join($tests_dir, 'gallery_core')
			);
		}

		return $suites;
	}


	/**
	 * Checks whether requirements have been met
	 */
	function are_requirements_met()
	{
		return (($this->has_required_memory_limit() && $this->has_required_software_versions()));
	}


	/**
	 * Renders a notice that the system requirements are not met
	 */
	function render_requirements_not_met()
	{
		include(path_join(
			$this->directory_path('templates'),
			'requirements_not_met.php'
		));
	}


	/**
	 * Ensures that the PHP memory limit is 16MB or above
	 * @return boolean
	 */
	function has_required_memory_limit()
	{
		$retval = TRUE;

        // Get the real memory limit before some increase it
		$this->memory_limit = ini_get('memory_limit');

		// If memory limit is specified in MB
		if (strtolower( substr($this->memory_limit, -1) ) == 'm') {
            $this->memory_limit = (int) substr( $this->memory_limit, 0, -1);

    		// Ensure that the memory limit is greater or equal to our minimum
    		if ( ($this->memory_limit != 0) && ($this->memory_limit < $this->_minimum_memory_limit ) ) {
				$retval = FALSE;
    		}
        }

		return $retval;
	}

	/**
	 * Checks whether the required WordPress version has been met
	 * @global string $wp_version
	 * @return boolean
	 */
	function has_required_software_versions()
	{
		global $wp_version;

		// Check for WP version installation
		return version_compare($wp_version, $this->_minimum_wordpress_version, '>=');
	}


	/**
	 * Returns the path to a file within the plugin root folder
	 * @param type $file_name
	 * @return type
	 */
	function file_path($file_name=NULL)
	{
		$location = $this->get_plugin_location();
		$path = dirname(__FILE__);

		if ($file_name != null)
		{
			$path .= '/' . $file_name;
		}

		return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	}


	/**
	 * Gets the directory path used by the plugin
	 * @return string
	 */
	function directory_path($dir=NULL)
	{
		return $this->file_path($dir);
	}


	/**
	 * Determines the location of the plugin - within a theme or plugin
	 * @return string
	 */
	function get_plugin_location()
	{
		$path = dirname(__FILE__);
		$gallery_dir = strtolower($path);
		$gallery_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $gallery_dir);

		$theme_dir = strtolower(get_stylesheet_directory());
		$theme_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $theme_dir);

		$plugin_dir = strtolower(WP_PLUGIN_DIR);
		$plugin_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $plugin_dir);

		$common_dir_theme = substr($gallery_dir, 0, strlen($theme_dir));
		$common_dir_plugin = substr($gallery_dir, 0, strlen($plugin_dir));

		if ($common_dir_theme == $theme_dir)
		{
			return 'theme';
		}

		if ($common_dir_plugin == $plugin_dir)
		{
			return 'plugin';
		}

		$parent_dir = dirname($path);

		if (file_exists($parent_dir . DIRECTORY_SEPARATOR . 'style.css'))
		{
			return 'theme';
		}

		return 'plugin';
	}


	/**
	 * Gets the URI for a particular path
	 * @param string $path
	 * @param boolean $url_encode
	 * @return string
	 */
	function path_uri($path = null, $url_encode = false)
	{
		$location = $this->get_plugin_location();
		$uri = null;

		$path = str_replace(array('/', '\\'), '/', $path);

		if ($url_encode)
		{
			$path_list = explode('/', $path);

			foreach ($path_list as $index => $path_item)
			{
				$path_list[$index] = urlencode($path_item);
			}

			$path = implode('/', $path_list);
		}

		if ($location == 'theme')
		{
			$theme_uri = get_stylesheet_directory_uri();

			$uri = $theme_uri . 'nextgen-gallery';

			if ($path != null)
			{
				$uri .= '/' . $path;
			}
		}
		else
		{
			// XXX Note, paths could not match but STILL being contained in the theme (i.e. WordPress returns the wrong path for the theme directory, either with wrong formatting or wrong encoding)
			$base = basename(dirname(__FILE__));

			if ($base != 'nextgen-gallery')
			{
				// XXX this is needed when using symlinks, if the user renames the plugin folder everything will break though
				$base = 'nextgen-gallery';
			}

			if ($path != null)
			{
				$base .= '/' . $path;
			}

			$uri = plugins_url($base);
		}

		return $uri;
	}

	/**
	 * Returns the URI for a particular file
	 * @param string $file_name
	 * @return string
	 */
	function file_uri($file_name = NULL)
	{
		return $this->path($file_name);
	}
}

new C_NextGEN_Bootstrap();
