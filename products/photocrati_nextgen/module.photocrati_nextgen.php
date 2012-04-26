<?php

/***
	{
		Module: photocrati-nextgen
	}
***/

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
	  
		$this->_get_registry()->add_module_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules', true, true);
	}
}

new P_Photocrati_NextGen();
