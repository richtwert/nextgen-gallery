<?php

/*
{
	Module:		photocrati-lazy_resources,
	Depends:	{ photocrati-router }
}
*/

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
	 * Registers the lazy resource loader utility
	 */
	function _register_utilities()
	{
		$this->get_registry()->add_utility(
			'I_Lazy_Resource_Loader', 'C_Lazy_Resource_Loader'
		);
	}

	/**
	 * Registers hooks for the WordPress platform
	 */
	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_scripts'), 1);
		add_action('wp_print_footer_scripts', array(&$this, 'print_footer_scripts'), 1);
		add_action('admin_print_footer_scripts', array(&$this, 'print_footer_scripts'), 1);
	}

	/**
	 * Uses WordPress enqueue mechanism to load lazy loader
	 * @uses init action
	 */
	function enqueue_scripts()
	{
		$router = $this->get_registry()->get_utility('I_Router');

		// Register SidJS: http://www.diveintojavascript.com/projects/sidjs-load-javascript-and-stylesheets-on-demand
		wp_register_script(
			'sidjs',
			$router->get_static_url('lazy_resources#sidjs-0.1.js'),
			array('jquery'),
			'0.1'
		);

		wp_register_script(
			'lazy_resources',
			$router->get_static_url('lazy_resources#lazy_resources.js'),
			array('sidjs', 'jquery'),
			$this->module_version
		);

		// Enqueue!
		wp_enqueue_script('lazy_resources');
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
		$loader = $this->get_registry()->get_utility('I_Lazy_Resource_Loader');
		$loader->enqueue();
	}


}

new M_Lazy_Resources();
