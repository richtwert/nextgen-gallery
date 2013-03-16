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

    function set_file_list()
    {
        return array(
            'adapter.router_settings.php',
            'adapter.routing_app_factory.php',
            'class.router.php',
            'class.http_response_controller.php',
            'class.routing_app.php',
            'interface.router.php',
            'interface.http_response.php',
            'interface.routing_app.php',
            'mixin.url_manipulation.php'
        );
    }
}

new M_Router;