<?php

/***
	{
		Module: photocrati-resource_loader,
		Depends: { photocrati-mvc }
	}
***/

class M_Resource_Loader extends C_Base_Module
{
    function define()
    {
        parent::define(
			'photocrati-resource_loader',
            'Resource Loader',
            'Provides stylesheets/scripts that are dynamic and can be modified by other
             modules',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }


    function initialize()
    {
        $this->_add_routes();

    }


    function _add_routes()
    {
		$router = $this->_get_router();
		$router->add_route(
			__CLASS__,
			'C_Resource_Loader',
			array(
				'uri' => $router->routing_pattern('resource_loader')
			)
		);
    }


    function _register_utilities()
    {
        $this->_get_registry()->add_utility('I_Resource_Loader', 'C_Resource_Loader');
    }


	function _get_router()
	{
		return $this->_get_registry()->get_utility('I_Router');
	}
}

new M_Resource_Loader();
