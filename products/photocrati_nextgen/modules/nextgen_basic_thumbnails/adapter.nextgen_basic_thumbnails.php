<?php

class A_NextGen_Basic_Thumbnails extends Mixin
{
	function initialize()
	{
		if ($this->object->name == 'nextgen_basic_thumbnails') {
			$this->object->add_pre_hook(
				'validate',
				get_class(),
				'Hook_NextGen_Basic_Thumbnails_Validation'
			);
		}
	}
}

class Hook_NextGen_Basic_Thumbnails_Validation extends Hook
{
	function validate()
	{
		// Set defaults
		if (!isset($this->object->template)) $this->object->template = '';
		if (!isset($this->object->images_per_page)) $this->images_per_page = FALSE;
	}
}