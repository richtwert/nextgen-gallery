<?php

class A_Router_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('router_param_separator', '--');
		$this->object->set_default('router_param_prefix',	 '');
		$this->object->set_default('router_param_slug',		 'params');
	}
}