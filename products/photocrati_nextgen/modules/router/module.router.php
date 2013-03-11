<?php
/*
{
	Module: photocrati-router,
	Depends: { photocrati-settings, photocrati-fs }
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

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Component_Factory', 'A_Routing_App_Factory');
		$this->get_registry()->add_adapter('I_Settings_Manager', 'A_Router_Settings');
	}
}

new M_Router;