<?php

class A_Dynamic_Stylesheet_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Add Dynamic Stylesheet Route',
			get_class(),
			'add_dynamic_stylesheet_route'
		);
	}

	function add_dynamic_stylesheet_route()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$app	= $router->create_app('/dcss');
		$app->rewrite('/{\d}/{*}', '/index--{1}/data--{2}');
		$app->route('/', 'I_Dynamic_Stylesheet#index');
	}
}