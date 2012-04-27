<?php

/***
 {
	Module: photocrati-gallery_storage
 }
***/
class M_Gallery_Storage extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-gallery_storage',
			'Gallery Storage',
			'Provides an abstraction layer for gallery image storage',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_adapters()
	{
		$this->_get_registry()->add_adapter('I_Component_Factory', 'A_GalleryStorage_Factory');
	}

	function _register_utilities()
	{
		$this->_get_registry()->add_utility('I_Gallery_Storage', 'C_Gallery_Storage');
	}
}

new M_Gallery_Storage();