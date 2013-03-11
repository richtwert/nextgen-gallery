<?php

class A_Dynamic_Stylesheet_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('dynamic_stylesheet_slug', 'dcss');
	}
}