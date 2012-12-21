<?php

class Hook_MediaRSS_Routes extends Hook
{
    /**
     * Adds MediaRSS routes
     */
    function add_routes()
    {
        $app = $this->get_registry()->get_utility('I_Router')->create_app();
        $app->route(
            array('/mediarss'),
            array(
                'controller' => 'C_MediaRSS_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );
    }
}