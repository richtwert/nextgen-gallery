<?php

class A_Activator_Rendering extends Mixin
{
	function initialize()
	{
		$this->object->add_mixin('Mixin_MVC_Controller_Rendering');
	}
}