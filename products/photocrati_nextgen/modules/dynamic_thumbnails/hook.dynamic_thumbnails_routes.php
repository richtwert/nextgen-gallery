<?php

class Hook_Dynamic_Thumbnails_Routes extends Hook
{
    /**
     * Adds dynamic-thumbnails routes
     */
    function add_routes()
    {
        $router = $this->get_registry()->get_utility('I_Router');
        $dynthumbs = $this->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
        $app = $router->create_app();
        $app->route(
            array('/' . $dynthumbs->get_route_name()),
            array(
                'controller' => 'C_Dynamic_Thumbnails_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );
    }
}