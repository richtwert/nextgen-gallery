<?php

/***
	{
		Module: photocrati-nextgen-legacy
	}
 ***/

define(
	'NEXTGEN_GALLERY_NGGLEGACY_MOD_DIR',
	path_join(NEXTGEN_GALLERY_MODULE_DIR, basename(dirname(__FILE__)))
);

define(
	'NEXTGEN_GALLERY_NGGLEGACY_MOD_URL',
	path_join(NEXTGEN_GALLERY_MODULE_URL, basename(dirname(__FILE__)))
);

class M_NggLegacy extends C_Base_Module
{
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

	function initialize()
	{
		parent::initialize();
		include_once(path_join(dirname(__FILE__), 'nggallery.php'));
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Installer', 'A_NggLegacy_Installer'
		);
	}
}

new M_NggLegacy();
