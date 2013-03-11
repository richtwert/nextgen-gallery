<?php

class A_MVC_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default(
			'mvc_module_dir',		dirname(__FILE__)
		);
		$this->object->set_default(
			'mvc_template_dir',
			path_join($this->object->get('mvc_module_dir'), 'templates')
		);
		$this->object->set_default(
			'mvc_template_dirname',	'/templates'
		);
		$this->object->set_default(
			'mvc_static_dirname',	'/static'
		);
	}
}