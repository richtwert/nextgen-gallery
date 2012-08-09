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

		// We have a dynamic resource loader for loading static content, such as
		// stylesheets and javascript source files.
		$this->_get_registry()->load_module('photocrati-resource_loader');

		// Add the admin area
		$this->_get_registry()->load_module('photocrati-admin');

		// Load the Ngglegacy ImageBrowser Gallery Type
		$this->_get_registry()->load_module('photocrati-nextgen_imagebrowser');

		// Load the NggLegacy Slideshow Gallery Type
		// TODO: We need to add the config class for the gallery type before
		// we can actually use/load it
		// $this->_get_registry()->load_module('photocrati-nextgen_slideshow');

		// Load the Attach to Post interface
		$this->_get_registry()->load_module('photocrati-attach_to_post');
	}
}

new P_Photocrati_NextGen();
