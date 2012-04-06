<?php

/***
	{
		Module: photocrati-resource_loader,
		Depends: { photocrati-mvc }
	}
***/

define(
    'PHOTOCRATI_GALLERY_MOD_RESOURCE_LOADER_ROUTING_PATTERN',
    "/\/resource_loader\/?([^\?]*)/"
);

define('PHOTOCRATI_GALLERY_MOD_RESOURCE_LOAD_DYNAMIC_STYLES_URL', path_join(
    site_url(), 'resource_loader/dynamic_styles'
));

define('PHOTOCRATI_GALLERY_MOD_RESOURCE_LOAD_DYNAMIC_SCRIPTS_URL', path_join(
    site_url(), 'resource_loader/dynamic_scripts'
));

define('PHOTOCRATI_GALLERY_MOD_RESOURCE_LOADER_ID', hash('md5', photocrati_gallery_plugin_serialize(array(
    $_SERVER['REQUEST_URI'], $_REQUEST
))));


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
        $router = $this->_get_registry()->get_singleton_utility('I_Router');
        $router->add_route(
            __CLASS__,
            'C_Resource_Loader', array(
                'uri'=>PHOTOCRATI_GALLERY_MOD_RESOURCE_LOADER_ROUTING_PATTERN
            )
        );
    }
    
    
    function _register_hooks()
    {
    }
    
    
    function _register_utilities()
    {
        $this->_get_registry()->add_utility('I_Resource_Loader', 'C_Resource_Loader');
    }
}

new M_Resource_Loader();
