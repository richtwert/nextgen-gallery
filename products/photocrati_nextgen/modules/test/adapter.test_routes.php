<?php

class A_Test_Routes extends Mixin
{
	function initialize()
	{
		$app = $this->object->create_app('/test');
		$app->route('/', 'I_Test_Controller#index');
	}
}