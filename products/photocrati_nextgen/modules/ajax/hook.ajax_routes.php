<?php

class Hook_AJAX_Routes extends Hook
{
    /**
     * Adds AJAX routes
     */
    function add_routes()
    {
        $router = $this->get_registry()->get_utility('I_Router');
        $app = $router->create_app();

        // TODO: fix this for wordpress installations in a sub-folder
        $ajax_url = '/photocrati_ajax';
        $js_url = $ajax_url . '/js';

        define('NEXTGEN_GALLERY_AJAX_URL', $ajax_url);
        define('NEXTGEN_GALLERY_AJAX_JS_URL', $js_url);

        $app->route(
            array($js_url),
            array(
                'controller' => 'C_Ajax_Controller',
                'action'  => 'js',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );

        $app->route(
            array($ajax_url),
            array(
                'controller' => 'C_Ajax_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET', 'POST')
            )
        );
    }

}