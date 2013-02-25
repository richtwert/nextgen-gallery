<?php

class A_Ajax_Settings extends Mixin
{
	function initialize()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$this->object->set('ajax_url',		$router->get_url('/photocrati_ajax/', FALSE));
		$this->object->set('ajax_js_url',	$router->get_url('/photocrati_ajax/js', FALSE));
	}
}