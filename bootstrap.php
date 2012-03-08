<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Plugin Name: NextGEN by Photocrati
 * Description: Providing you the best gallery management for WordPress
 * Version: 0.1
 * Plugin URI: http://www.photocrati.com
 * Author URI: http://www.photocrati.com
 */

// Define constants
define('PHOTOCRATI_GALLERY_PLUGIN_DIR', photocrati_gallery_plugin_directory());
define('PHOTOCRATI_GALLERY_PLUGIN_URL', photocrati_gallery_plugin_path_uri());
define('PHOTOCRATI_GALLERY_I8N_DOMAIN', 'pc');
define('PHOTOCRATI_GALLERY_MODULE_DIR', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'modules'));
define('PHOTOCRATI_GALLERY_MODULE_URL', path_join(PHOTOCRATI_GALLERY_PLUGIN_URL, 'modules'));
define('PHOTOCRATI_GALLERY_PRODUCT_DIR', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'products'));
define('PHOTOCRATI_GALLERY_PLUGIN_CLASS', path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'class.photocrati_gallery_plugin.php'));
define('PHOTOCRATI_GALLERY_PLUGIN_STARTED_AT', microtime());
$upload_paths = wp_upload_dir();
define('PHOTOCRATI_GALLERY_STORAGE_PATH', $upload_paths['basedir']);
//error_reporting(E_ALL);
//@ini_set('display_errors', 'On');


function photocrati_gallery_plugin_location()
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

function photocrati_gallery_plugin_file_path($file_name = null)
{
	$location = photocrati_gallery_plugin_location();
	$path = dirname(__FILE__);

	if ($file_name != null)
	{
		$path .= '/' . $file_name;
	}

	return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
}

function photocrati_gallery_plugin_directory()
{
	return photocrati_gallery_plugin_file_path();
}

function photocrati_gallery_plugin_path_uri($path = null, $url_encode = false)
{
	$location = photocrati_gallery_plugin_location();
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

		$uri = $theme_uri . 'nextgen';

		if ($path != null)
		{
			$uri .= '/' . $path;
		}
	}
	else
	{
		// XXX Note, paths could not match but STILL being contained in the theme (i.e. WordPress returns the wrong path for the theme directory, either with wrong formatting or wrong encoding)
		$base = basename(dirname(__FILE__));

		if ($base != 'nextgen')
		{
			// XXX this is needed when using symlinks, if the user renames the plugin folder everything will break though
			$base = 'nextgen';
		}

		if ($path != null)
		{
			$base .= '/' . $path;
		}

		$uri = plugins_url($base);
	}

	return $uri;
}

function photocrati_gallery_plugin_file_uri($file_name = null)
{
	return photocrati_gallery_plugin_path_uri($file_name);
}

// Using json_encode here because PHP's serialize is not Unicode safe
function photocrati_gallery_plugin_serialize($value)
{
	return json_encode($value);
}

// Using json_decode here because PHP's unserialize is not Unicode safe
function photocrati_gallery_plugin_unserialize($value)
{
	return json_decode($value);
}

// Instantiate plugin on init
add_action('init', 'photocrati_gallery_init', 100);
function photocrati_gallery_init() {
    // Include pope framework
    include_once(path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, implode(
        DIRECTORY_SEPARATOR, array('pope','lib','autoload.php')
    )));

	// Include NextGen Legacy
    include_once('nggallery.php');

    // Include some extra helpers
    include_once(path_join(PHOTOCRATI_GALLERY_PLUGIN_DIR, 'wordpress_helpers.php'));

    // Include and instantiate the WordPress plugin
    include_once(PHOTOCRATI_GALLERY_PLUGIN_CLASS);
    new C_Photocrati_Gallery_Plugin();
}
