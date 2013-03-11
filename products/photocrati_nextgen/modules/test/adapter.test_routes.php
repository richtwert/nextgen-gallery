<?php

class A_Test_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			get_class(),
			get_class(),
			'add_test_routes'
		);
	}

	function add_test_routes()
	{
		$app = $this->object->create_app('/test');
		$app->route('/', 'I_Test_Controller#index');
	}
}