<?php
/*
{
	Module: photocrati-installer,
	Depends: { photocrati-settings }
}
 */
class M_Installer extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-installer',
			'Installer',
			'Provides an installer for modules to use',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Installer', 'C_Module_Installer');
	}
}

new M_Installer;