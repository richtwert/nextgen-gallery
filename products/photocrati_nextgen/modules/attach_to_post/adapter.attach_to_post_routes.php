<?php

class A_Attach_To_Post_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Attach To Post Routes',
			get_class(),
			'add_attach_to_post_routes'
		);
	}

	function add_attach_to_post_routes()
	{
		$router   = $this->object->get_registry()->get_utility('I_Router');
		$app = $router->create_app('/attach_to_post');
		$app->rewrite('/preview/{id}',			'/preview/id--{id}');
		$app->rewrite('/display_tab_js/{id}',	'/display_tab_js/id--{id}');
		$app->rewrite('/{id}',					'/id--{id}');
		$app->route('/preview',			'I_Attach_To_Post_Controller#preview');
		$app->route('/display_tab_js',	'I_Attach_To_Post_Controller#display_tab_js');
		$app->route('/',				'I_Attach_To_Post_Controller#index');
	}
}