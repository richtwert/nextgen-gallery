<?php

/***
	{
		Module: photocrati-lazy_resources
	}
***/

define(
	'PHOTOCRATI_GALLERY_LAZY_RESOURCES_JS_URL',
	PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js'
);

class M_Lazy_Resources extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-lazy_resources',
			'Lazy Resources',
			'Lazy-loads enqueued static resources (stylesheets, scripts) at runtime',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Registers hooks for the WordPress platform
	 */
	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_scripts'));
		add_action('wp_print_footer_scripts', array(&$this, 'print_footer_scripts'), 999);
	}

	/**
	 * Uses WordPress enqueue mechanism to load lazy loader
	 * @uses init action
	 */
	function enqueue_scripts()
	{
		// Register SidJS: http://www.diveintojavascript.com/projects/sidjs-load-javascript-and-stylesheets-on-demand
		wp_register_script(
			'sidjs',
			PHOTOCRATI_GALLERY_LAZY_RESOURCES_JS_URL.'/sidjs-0.1.js',
			array(),
			'0.1',
			TRUE
		);

		// Enqueue!
		wp_enqueue_script('sidjs');
	}


	/**
	 * Sometimes scripts and stylesheets get enqueued too late to be added
	 * to the header, but still need to be loaded. In the case of stylesheets,
	 * link tags can only be contained in the header.
	 *
	 * So, we'll tell the lazy loader to load our scripts
	 */
	function print_footer_scripts()
	{
		// Get the remaining script and style resources that haven't yet
		// been loaded
		ob_start();
		wp_print_scripts();
		$script_urls = $this->_parse_resource_urls(ob_get_contents());
		ob_end_clean();
		ob_start();
		wp_print_styles();
		$style_urls = $this->_parse_resource_urls(ob_get_contents());
		ob_end_clean();

		// Lazy-load all resources
		echo "\n<script type='text/javascript'>";
		echo "var urls = ".json_encode($script_urls).";\n";
		echo "if (urls.length) Sid.js(urls);\n";
		echo "urls = ".json_encode($style_urls).";\n";
		echo "if (urls.length) Sid.css(urls);\n";
		echo "</script>\n";
	}

	/**
	 * Parses HTML for urls of static resources
	 */
	function _parse_resource_urls($html)
	{
		$urls = array();
		if (preg_match_all("/(src|href)=['\"]([^'\"]+)/", $html, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				if (isset($match[2])) {
					$urls[] = $match[2];
				}
			}
		}
		return $urls;
	}
}

new M_Lazy_Resources();