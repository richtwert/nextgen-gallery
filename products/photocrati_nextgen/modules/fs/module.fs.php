<?php
/*
{
	Module: photocrati-fs
}
 */
class M_Fs extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-fs',
			'Filesystem',
			'Provides a filesystem abstraction layer for Pope modules',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Fs', 'C_Fs');
	}
}

new M_Fs;