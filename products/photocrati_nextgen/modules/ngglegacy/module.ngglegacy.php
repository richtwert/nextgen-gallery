<?php

/***
	{
		Module: photocrati-nextgen-legacy
	}
 ***/
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
		include_once(path_join(__DIR__, 'nggallery.php'));
	}
}

new M_NggLegacy();