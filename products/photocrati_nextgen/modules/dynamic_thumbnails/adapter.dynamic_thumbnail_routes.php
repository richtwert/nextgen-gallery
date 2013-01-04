<?php

class A_Dynamic_Thumbnail_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Dynamic Thumbnail routes',
			get_class(),
			'add_dynamic_thumbnail_routes'
		);
	}

	function add_dynamic_thumbnail_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');
        $app = $router->create_app('/nextgen_image');
		$app->rewrite('/{\w}/{\w}/{\w}', '/nggallery/id--{1}/size--{2}/manip--{3}', FALSE, TRUE);
        $app->route('/', 'I_Dynamic_Thumbnails_Controller#index');
	}
}