<?php
/*
{
	Module: photocrati-router
}
 */
class M_Router extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-router',
			'Router for Pope',
			'Provides routing capabilities for Pope modules',
			'0.2',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Router', 'C_Router');
	}
}

new M_Router;