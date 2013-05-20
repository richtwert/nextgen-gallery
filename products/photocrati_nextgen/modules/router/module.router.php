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

    function get_type_list()
    {
        return array(
            'A_Router_Settings' => 'adapter.router_settings.php',
            'A_Routing_App_Factory' => 'adapter.routing_app_factory.php',
            'C_Router' => 'class.router.php',
            'C_Http_Response_Controller' => 'class.http_response_controller.php',
            'C_Routing_App' => 'class.routing_app.php',
            'I_Router' => 'interface.router.php',
            'I_Http_Response' => 'interface.http_response.php',
            'I_Routing_App' => 'interface.routing_app.php',
            'Mixin_Url_Manipulation' => 'mixin.url_manipulation.php'
        );
    }
}

new M_Router;
