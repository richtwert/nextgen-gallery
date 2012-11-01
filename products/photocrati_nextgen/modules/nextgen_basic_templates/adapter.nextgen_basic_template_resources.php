<?php

class A_NextGen_Basic_Template_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueues resources required for NextGEN template widget',
			__CLASS__,
			'enqueue_nextgen_basic_template_resources'
		);
	}

	function enqueue_nextgen_basic_template_resources()
	{
	}
}
