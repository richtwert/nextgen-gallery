<?php

class A_Dynamic_Thumbnail_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Dynamic Thumbnail routes',
			get_class(),
			'add_dynamic_thubmnail_routes'
		);
	}

	function add_dynamic_thumbnail_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');
        $dynthumbs = $this->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
        $app = $router->create_app($dynthumbs->get_route_name());
        $app->route('/', 'I_Dynamic_Thumbnails_Controller#index');
	}
}