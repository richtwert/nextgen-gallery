<?php

class Hook_AJAX_Routes extends Hook
{
    /**
     * Adds AJAX routes
     */
    function add_routes()
    {
        $router = $this->get_registry()->get_utility('I_Router');
		$app	= $router->create_app('/photocrati_ajax');
		$app->route('/',	'I_Ajax_Controller#index');
		$app->route('/js',	'I_Ajax_Controller#js');
    }
}