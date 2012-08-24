<?php

/***
	{
		Module: photocrati-nextgen-legacy
	}
 ***/

define(
	'PHOTOCRATI_GALLERY_NGGLEGACY_MOD_DIR',
	path_join(PHOTOCRATI_GALLERY_MODULE_DIR, basename(dirname(__FILE__)))
);

define(
	'PHOTOCRATI_GALLERY_NGGLEGACY_MOD_URL',
	path_join(PHOTOCRATI_GALLERY_MODULE_URL, basename(dirname(__FILE__)))
);

class M_NggLegacy extends C_Base_Module
{
	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen-legacy',
			'NextGEN Legacy',
			'Embeds the original version of NextGEN 1.9.3 by Alex Rabe',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		include_once(path_join(dirname(__FILE__), 'nggallery.php'));
	}
}

new M_NggLegacy();