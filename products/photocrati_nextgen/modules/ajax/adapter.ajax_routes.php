<?php

class A_Ajax_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds AJAX Routes',
			get_class(),
			'add_ajax_routes'
		);
	}

	function add_ajax_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$app	= $router->create_app('/photocrati_ajax');
		$app->route('/js',	'I_Ajax_Controller#js');
		$app->route('/',	'I_Ajax_Controller#index');
	}
}