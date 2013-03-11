<?php

class A_Ajax_Settings extends Mixin
{
	function initialize()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$slug = '/photocrati_ajax';
		$this->object->set_default('ajax_slug',		$slug);
		$this->object->set_default('ajax_url',		$router->get_url($slug, FALSE));
		$this->object->set_default('ajax_js_url',	$router->get_url('/'.$slug.'/js', FALSE));
	}
}