<?php

/***
	{
		Product: photocrati-nextgen
	}
***/

define('PHOTOCRATI_GALLERY_CHANGE_OPTIONS_CAP', 'NextGEN Manage gallery');

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
		$this->_get_registry()->set_product_module_path($this->module_id, $module_path);
		$this->_get_registry()->add_module_path($module_path, TRUE, FALSE);
		
		// The NextGEN Settings module provides uniform centralized management
		// of settings for the plugin.
		$this->_get_registry()->load_module('photocrati-nextgen_settings');

		// The datamapper is a library which is required by our data tier
		// components. This is the first module we load as in the future, the
		// plan is to refactor the photocrati-nextgen-legacy module to use it
		$this->_get_registry()->load_module('photocrati-datamapper');

		// We load the data tier module for NextGen. This is built on top of
		// the photocrati-datamapper module. Other than the photocrati-nextgen-legacy
		// module, all other modules require this. Eventually, we will refactor
		// the photocrati-nextgen-legacy module to make use of this module as
		// well
		$this->_get_registry()->load_module('photocrati-nextgen-data');

		// This is Alex Rabe's version of NextGEN, which we built on top of.
		$this->_get_registry()->load_module('photocrati-nextgen-legacy');

		// The MVC framework is really a templating framework - not MVC.
		$this->_get_registry()->load_module('photocrati-mvc');

		// This provides a general AJAX handler for all other modules to extend
		$this->_get_registry()->load_module('photocrati-ajax');

		// Load the Lazy Resource Loader
		$this->_get_registry()->load_module('photocrati-lazy_resources');

		// Load the Gallery Display module, used to display galleries and albums
		$this->_get_registry()->load_module('photocrati-gallery_display');

		// Load MediaRSS module. Required by the NextGEN Basic Thumbnails display type
		$this->_get_registry()->load_module('photocrati-mediarss');

		// Load the NextGEN Basic Thumbnails display type
		$this->_get_registry()->load_module('photocrati-nextgen_basic_thumbnails');
	}
}

new P_Photocrati_NextGen();
