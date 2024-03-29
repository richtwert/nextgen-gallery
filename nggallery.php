<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Plugin Name: NextGEN Gallery by Photocrati
 * Description: The most popular gallery plugin for WordPress and one of the most popular plugins of all time with over 7 million downloads.
 * Version: 2.0-beta1
 * Author: Photocrati Media
 * Plugin URI: http://www.nextgen-gallery.com
 * Author URI: http://www.photocrati.com
 */

/**
 * NextGEN Gallery is built on top of the Photocrati Pope Framework:
 * https://bitbucket.org/photocrati/pope-framework
 *
 * Pope constructs applications by assembling modules.
 *
 * The Bootstrapper. This class performs the following:
 * 1) Loads the Pope Framework
 * 2) Adds a path to the C_Component_Registry instance to search for products
 * 3) Loads all found Products. A Product is a collection of modules with some
 * additional meta data. A Product is responsible for loading any modules it
 * requires.
 * 4) Once all Products (and their associated modules) have been loaded (or in
 * otherwords, "included"), the modules are initialized.
 */

class C_NextGEN_Bootstrap
{
	var $_registry = NULL;

	function __construct()
	{
		// Boostrap
		$this->_define_constants();
		$this->_load_pope();
		$this->_register_hooks();

	}

	/**
	 * Loads the Pope Framework
	 */
	function _load_pope()
	{
		// Pope requires a a higher limit
        $tmp = ini_get('xdebug.max_nesting_level');
        if ($tmp && (int)$tmp <= 300) @ini_set('xdebug.max_nesting_level', 300);

		// Include pope framework
		require_once(path_join(NEXTGEN_GALLERY_PLUGIN_DIR, implode(
			DIRECTORY_SEPARATOR, array('pope','lib','autoload.php')
		)));

		// Include some extra helpers
		require_once(path_join(NEXTGEN_GALLERY_PLUGIN_DIR, 'wordpress_helpers.php'));

		// Get the component registry
		$this->_registry = C_Component_Registry::get_instance();

		// Add the default Pope factory utility, C_Component_Factory
		$this->_registry->add_utility('I_Component_Factory', 'C_Component_Factory');

		// Load embedded products. Each product is expected to load any
		// modules required
		$this->_registry->add_module_path(NEXTGEN_GALLERY_PRODUCT_DIR, true, false);
		$this->_registry->load_all_products();

        // Give third-party plugins that opportunity to include their own products
        // and modules
        do_action('load_nextgen_gallery_modules', $this->_registry);

		// Initializes all loaded modules
		$this->_registry->initialize_all_modules();

		// Set the document root
		$this->_registry->get_utility('I_Fs')->set_document_root(ABSPATH);
	}


	/**
	 * Registers hooks for the WordPress framework necessary for instantiating
	 * the plugin
	 */
	function _register_hooks()
	{
		// Load text domain
		load_plugin_textdomain(
			NEXTGEN_GALLERY_I8N_DOMAIN,
			false,
			$this->directory_path('lang')
		);

		// Register the activation routines
		add_action('activate_'.NEXTGEN_GALLERY_PLUGIN_BASENAME, array(get_class(), 'activate'));

		// Register the deactivation routines
		add_action('deactivate_'.NEXTGEN_GALLERY_PLUGIN_BASENAME, array(get_class(), 'deactivate'));

		// Register our test suite
		add_filter('simpletest_suites', array(&$this, 'add_testsuite'));

		// Start the plugin!
		add_action('init', array(&$this, 'route'), 99);
	}

	/**
	 * Routes access points using the Pope Router
	 * @return boolean
	 */
	function route()
	{
		$router = $this->_registry->get_utility('I_Router');
		if (!$router->serve_request() && $router->has_parameter_segments()) {
			return $router->passthru();
		}
	}

	private static function get_pope_installer()
	{
		$registry	= C_Component_Registry::get_instance();
		return $installer	= $registry->get_utility('I_Installer');
	}

	/**
	 * Run the installer
	 */
	static function activate($network=FALSE)
	{
		self::get_pope_installer()->install(NEXTGEN_GALLERY_PLUGIN_BASENAME);
	}

	/**
	 * Run the uninstaller
	 */
	static function deactivate()
	{
		self::get_pope_installer()->uninstall(NEXTGEN_GALLERY_PLUGIN_BASENAME);
	}

	/**
	 * Defines necessary plugins for the plugin to load correctly
	 */
	function _define_constants()
	{
		// NextGEN by Photocrati Constants
		define('NEXTGEN_GALLERY_PLUGIN', basename($this->directory_path()));
		define('NEXTGEN_GALLERY_PLUGIN_BASENAME', plugin_basename(__FILE__));
		define('NEXTGEN_GALLERY_PLUGIN_DIR', $this->directory_path());
		define('NEXTGEN_GALLERY_PLUGIN_URL', $this->path_uri());
		define('NEXTGEN_GALLERY_I8N_DOMAIN', 'nggallery');
		define('NEXTGEN_GALLERY_TESTS_DIR', path_join(NEXTGEN_GALLERY_PLUGIN_DIR, 'tests'));
		define('NEXTGEN_GALLERY_PRODUCT_DIR', path_join(NEXTGEN_GALLERY_PLUGIN_DIR, 'products'));
		define('NEXTGEN_GALLERY_PRODUCT_URL', path_join(NEXTGEN_GALLERY_PLUGIN_URL, 'products'));
		define('NEXTGEN_GALLERY_MODULE_DIR', path_join(NEXTGEN_GALLERY_PRODUCT_DIR, 'photocrati_nextgen/modules'));
		define('NEXTGEN_GALLERY_MODULE_URL', path_join(NEXTGEN_GALLERY_PRODUCT_URL, 'photocrati_nextgen/modules'));
		define('NEXTGEN_GALLERY_PLUGIN_CLASS', path_join(NEXTGEN_GALLERY_PLUGIN_DIR, 'module.NEXTGEN_GALLERY_PLUGIN.php'));
		define('NEXTGEN_GALLERY_PLUGIN_STARTED_AT', microtime());
		define('NEXTGEN_GALLERY_PLUGIN_VERSION', '2.0');
	}


	/**
	 * Defines the NextGEN Test Suite
	 * @param array $suites
	 * @return array
	 */
	function add_testsuite($suites=array())
	{
		$tests_dir = NEXTGEN_GALLERY_TESTS_DIR;

		if (file_exists($tests_dir)) {

			// Include mock objects
			// TODO: These mock objects should be moved to the appropriate
			// test folder
			require_once(path_join($tests_dir, 'mocks.php'));

			// Define the NextGEN Test Suite
            $suites['nextgen'] = array(
//                path_join($tests_dir, 'mvc'),
                path_join($tests_dir, 'datamapper'),
                path_join($tests_dir, 'nextgen_data'),
                path_join($tests_dir, 'gallery_display')
            );
        }

		return $suites;
	}


	/**
	 * Returns the path to a file within the plugin root folder
	 * @param type $file_name
	 * @return type
	 */
	function file_path($file_name=NULL)
	{
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
