<?php

class A_DataMapper_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('datamapper_driver', 'custom_post_datamapper');
	}
}