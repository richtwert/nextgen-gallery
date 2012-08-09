<?php

/***
{
	Module:	photocrati-nextgen_settings
}
***/

class M_NextGen_Settings extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_settings',
			'NextGEN Gallery Settings',
			'Provides central management for NextGEN Gallery settings',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}
	
	
	function _register_utilities()
	{
		$this->_get_registry()->add_utility('I_NextGen_Settings', 'C_NextGen_Settings');
	}
}

new M_NextGen_Settings();