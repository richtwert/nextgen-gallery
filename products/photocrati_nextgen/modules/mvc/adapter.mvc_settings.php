<?php

class A_MVC_Settings extends Mixin
{
	function initialize()
	{
		$mvc_module_dir = $this->object->set_default(
			'mvc_module_dir',		dirname(__FILE__)
		);
		$this->object->set_default(
			'mvc_template_dir',		path_join($mvc_module_dir, 'templates')
		);
		$this->object->set_default(
			'mvc_template_dirname',	'templates'
		);
		$this->object->set_default(
			'mvc_static_dirname',	'static'
		);
	}
}