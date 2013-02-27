<?php

/***
	{
		Product: photocrati-nextgen
	}
***/

define('NEXTGEN_GALLERY_CHANGE_OPTIONS_CAP', 'NextGEN Manage gallery');

class P_Photocrati_NextGen extends C_Base_Product
{
	function define()
	{
		parent::define(
			'photocrati-nextgen',
			'Photocrati NextGen',
			'Photocrati NextGen',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);

		$module_path = path_join(dirname(__FILE__), 'modules');
		$this->get_registry()->set_product_module_path($this->module_id, $module_path);
		$this->get_registry()->add_module_path($module_path, TRUE, FALSE);

		/*** GENERIC MODULES ***/

		// Load the FS module - no dependencies
		$this->get_registry()->load_module('photocrati-fs');

		// Load the settings manager module - no dependencies
		$this->get_registry()->load_module('photocrati-settings');

		// Load the installer - dependent on photocrati-settings
		$this->get_registry()->load_module('photocrati-installer');

		// Load the router - depends on photocrati-settings and photocrati-fs
		$this->get_registry()->load_module('photocrati-router');
		$this->get_registry()->load_module('photocrati-wordpress_routing');
//
//		// The validation module is a helper module intended to be adopted
//		// by other modules. The datamapper and nextgen_settings modules
//		// make use of it.
//		$this->get_registry()->load_module('photocrati-validation');
//
//		// load security module, to perform capability and nonce checks
//		$this->get_registry()->load_module('photocrati-security');

		// Load the LZW compression module, a useful utility
		$this->get_registry()->load_module('photocrati-lzw');

		// The MVC framework is really a templating framework - not MVC.
		// Dependent on photocrati-router
		$this->get_registry()->load_module('photocrati-mvc');
		$this->get_registry()->load_module('photocrati-test');

		// Load the dynamic stylesheet utility - dependent on photocrati-lzw
		// and photocrati-mvc
		$this->get_registry()->load_module('photocrati-dynamic_stylesheet');

		// This provides a general AJAX handler for all other modules to extend
		$this->get_registry()->load_module('photocrati-ajax');

		// The datamapper is a library which is required by our data tier
		// components. This is the first module we load as in the future, the
		// plan is to refactor the photocrati-nextgen-legacy module to use it
		$this->get_registry()->load_module('photocrati-datamapper');

		/*** NEXTGEN GALLERY MODULES ***/
		$this->get_registry()->load_module('photocrati-nextgen_settings');
//
//		// We load the data tier module for NextGen. This is built on top of
//		// the photocrati-datamapper module. Other than the photocrati-nextgen-legacy
//		// module, all other modules require this. Eventually, we will refactor
//		// the photocrati-nextgen-legacy module to make use of this module as
//		// well
//		$this->get_registry()->load_module('photocrati-nextgen-data');
//
//		// This is Alex Rabe's version of NextGEN, which we built on top of.
//		$this->get_registry()->load_module('photocrati-nextgen-legacy');
//
//
//		// Load the Lazy Resource Loader
//		$this->get_registry()->load_module('photocrati-lazy_resources');
//
//		// Provides a mechanism for Frame Communication
//		$this->get_registry()->load_module('photocrati-frame_communication');
//
//        // Provides cache clearing support
//        $this->get_registry()->load_module('photocrati-cache');
//
//		// Provides framework-wide support for thumbnail-like gallery types
//		$this->get_registry()->load_module('photocrati-dynamic-thumbnails');
//
//		// Load the Gallery Display module, used to display galleries and albums
//		$this->get_registry()->load_module('photocrati-gallery_display');
//		$this->get_registry()->load_module('photocrati-attach_to_post');
//
//		// Load various lightbox effect libraries
//		$this->get_registry()->load_module('photocrati-thickbox');
//        $this->get_registry()->load_module('photocrati-shutter');
//		$this->get_registry()->load_module('photocrati-shutter_reloaded');
//        $this->get_registry()->load_module('photocrati-highslide');
//        $this->get_registry()->load_module('photocrati-lightbox');
//        $this->get_registry()->load_module('photocrati-fancybox-1x');
//
//		// Load MediaRSS module. Required by the NextGEN Basic Thumbnails display type
//		$this->get_registry()->load_module('photocrati-mediarss');
//
//		// Provides support for thumbnail basic templates
//		$this->get_registry()->load_module('photocrati-nextgen_basic_templates');
//
//		// Load the NextGEN Basic display types
//		$this->get_registry()->load_module('photocrati-nextgen_basic_thumbnails');
//		$this->get_registry()->load_module('photocrati-nextgen_basic_slideshow');
//		$this->get_registry()->load_module('photocrati-nextgen_basic_imagebrowser');
//        $this->get_registry()->load_module('photocrati-nextgen_basic_singlepic');
//        $this->get_registry()->load_module('photocrati-nextgen_basic_tagcloud');
//        $this->get_registry()->load_module('photocrati-nextgen_basic_album');
//
//        // Provides sidebar widgets
//        $this->get_registry()->load_module('photocrati-widget');
//
//		// Provides jsconsole remote debugging support
//		$this->get_registry()->load_module('photocrati-jsconsole');
	}
}

new P_Photocrati_NextGen();
