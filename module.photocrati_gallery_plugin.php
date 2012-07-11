<?php

/***
	{
		Module: photocrati-gallery-core
	}
***/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * This is the meat and potatoes of the plugin.
 *
 * This plugin uses a component architecture, heavily based on what's used
 * in Zope 3: http://www.muthukadan.net/docs/zca.html
 */

class M_Photocrati_Gallery_Plugin extends C_Base_Module
{
    function define()
    {
        parent::define('photocrati-gallery-core', 'Photocrati Core');

        $this->_load_modules();
    }


	/**
	 * Registers necessary utilities foundamental to this plugin
	 */
    function _register_utilities()
    {
        $this->_get_registry()->add_utility('I_Component_Factory', 'C_Component_Factory');
        $this->_get_registry()->add_utility('I_Db',                'C_WordPress_Db');
    }

    /**
     * Loads all modules
     */
    function _load_modules()
    {
    	$this->_get_registry()->add_module_path(PHOTOCRATI_GALLERY_PRODUCT_DIR, true, true);;
    	$this->_get_registry()->initialize_all_modules();
    }


	/**
	 * Registers any fundamental hooks into WordPress
	 */
	function _register_hooks()
	{
		//Add some links on the plugin page
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
	}

	/**
	 * Adds links to the plugin page
	 * @param array $links
	 * @param type $file
	 * @return array
	 */
	function add_plugin_links($links, $file)
	{
		if ( $file == PHOTOCRATI_GALLERY_PLUGIN ) {

			// Link labels
			$links = array_merge($links, array(
				__('Overview',		PHOTOCRATI_GALLERY_I8N_DOMAIN) =>
				"admin.php?page=".PHOTOCRATI_GALLERY_PLUGIN,

				__('Get help',		PHOTOCRATI_GALLERY_I8N_DOMAIN) =>
				"http://wordpress.org/tags/nextgen-gallery?forum_id=10",

				__('Contribute',	PHOTOCRATI_GALLERY_I8N_DOMAIN) =>
				"http://bitbucket.org/photocrati/nextgen-gallery"
			));
		}
		return $links;
	}
}

new M_Photocrati_Gallery_Plugin();