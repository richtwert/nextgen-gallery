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

		// The validation module is a helper module intended to be adopted
		// by other modules. The datamapper module makes use of it for
		// example
		$this->get_registry()->load_module('photocrati-validation');

		// Load the settings manager module - no dependencies
		$this->get_registry()->load_module('photocrati-settings');

		// Load the installer - dependent on photocrati-settings
		$this->get_registry()->load_module('photocrati-installer');

		// Load the router - depends on photocrati-settings and photocrati-fs
		$this->get_registry()->load_module('photocrati-router');
		$this->get_registry()->load_module('photocrati-wordpress_routing');

		// load security module, to perform capability and nonce checks
		$this->get_registry()->load_module('photocrati-security');

		// Load the LZW compression module, a useful utility
		$this->get_registry()->load_module('photocrati-lzw');

		// The MVC framework is really a templating framework - not MVC.
		// Dependent on photocrati-router
		$this->get_registry()->load_module('photocrati-mvc');

		// Load the Lazy Resource Loader - dependent on photocrati-router
		$this->get_registry()->load_module('photocrati-lazy_resources');

		// Load the dynamic stylesheet utility - dependent on photocrati-lzw
		// and photocrati-mvc
		$this->get_registry()->load_module('photocrati-dynamic_stylesheet');

		// Provides a mechanism for Frame Communication - dependent on
		// photocrati-settings and photocrati-router
		$this->get_registry()->load_module('photocrati-frame_communication');

		// This provides a general AJAX handler for all other modules to extend
		$this->get_registry()->load_module('photocrati-ajax');

		// The datamapper is a library which is required by our data tier
		// components. This is the first module we load as in the future, the
		// plan is to refactor the photocrati-nextgen-legacy module to use it
		$this->get_registry()->load_module('photocrati-datamapper');

		/*** NEXTGEN GALLERY MODULES ***/
		$this->get_registry()->load_module('photocrati-nextgen_settings');

		// This is Alex Rabe's version of NextGEN, which we built on top of.
		$this->get_registry()->load_module('photocrati-nextgen-legacy');

		// We load the data tier module for NextGen. This is built on top of
		// the photocrati-datamapper module. Other than the photocrati-nextgen-legacy
		// module, all other modules require this. Eventually, we will refactor
		// the photocrati-nextgen-legacy module to make use of this module as
		// well.
		// Unfortunately, at this time as well, the photocrati-nextgen-data
		// module requires the ngglegacy module
		$this->get_registry()->load_module('photocrati-nextgen-data');

		// Provides framework-wide support for thumbnail-like gallery types
		// Depends on photocrati-nextgen_data
		$this->get_registry()->load_module('photocrati-dynamic_thumbnails');

		// Load the NextGEN Gallery Admin interface base classes
		$this->get_registry()->load_module('photocrati-nextgen_admin');

        // Load the NextGEN Gallery Admin pages
        $this->get_registry()->load_module('photocrati-nextgen_admin_pages');

        // Load the pagination module
        $this->_get_registry()->load_module('photocrati-nextgen_pagination');

		// Load the Gallery Display module, used to display galleries and albums
		$this->get_registry()->load_module('photocrati-nextgen_gallery_display');
		$this->get_registry()->load_module('photocrati-attach_to_post');

		// Load the NextGEN Gallery "Other Options" page
		$this->get_registry()->load_module('photocrati-nextgen_other_options');

		// Provides jsconsole remote debugging support
		$this->get_registry()->load_module('photocrati-jsconsole');

		// Load MediaRSS module - dependent on photocrati-nextgen_gallery_display
		$this->get_registry()->load_module('photocrati-mediarss');

        // Provides cache clearing support
        $this->get_registry()->load_module('photocrati-cache');

        // Provides a collection of lightbox libraries
        $this->get_registry()->load_module('photocrati-lightbox');

		// Provides support for thumbnail basic templates
		$this->get_registry()->load_module('photocrati-nextgen_basic_templates');

		// Load the NextGEN Basic display types
		$this->get_registry()->load_module('photocrati-nextgen_basic_gallery');
		$this->get_registry()->load_module('photocrati-nextgen_basic_imagebrowser');
        $this->get_registry()->load_module('photocrati-nextgen_basic_singlepic');
        $this->get_registry()->load_module('photocrati-nextgen_basic_tagcloud');
        $this->get_registry()->load_module('photocrati-nextgen_basic_album');

        // Provides sidebar widgets
        $this->get_registry()->load_module('photocrati-widget');
	}
}

new P_Photocrati_NextGen();

