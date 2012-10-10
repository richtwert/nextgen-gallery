<?php
/***
{
		Module: photocrati-cache
}
***/
class M_Cache extends C_Base_Module
{
    /**
     * Defines the module name & version
     */
    function define()
	{
		parent::define(
			'photocrati-cache',
			'Cache',
			'Handles clearing of NextGen caches',
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
	}
}

new M_Cache();
