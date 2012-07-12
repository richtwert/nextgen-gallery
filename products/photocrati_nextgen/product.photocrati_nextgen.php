<?php

/***
	{
		Product: photocrati-nextgen
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

		$module_path = path_join(dirname(__FILE__), 'modules');
		$this->_get_registry()->set_product_module_path($this->module_id, $module_path);
		$this->_get_registry()->add_module_path($module_path, TRUE, FALSE);
		$this->_get_registry()->load_module('photocrati-nextgen-legacy');
	}
}

new P_Photocrati_NextGen();
