<?php

class A_NextGen_Basic_Slideshow extends Mixin
{
	function initialize()
	{
		if ($this->object->name == NEXTGEN_GALLERY_BASIC_SLIDESHOW) {
			$this->object->add_pre_hook(
				'set_defaults',
				get_class(),
				'Hook_NextGen_Basic_Slideshow_Validation'
			);
		}
	}
}

class Hook_NextGen_Basic_Slideshow_Validation extends Hook
{
	function validation()
	{
		$this->object->validates_presence_of('gallery_width');
		$this->object->validates_presence_of('gallery_height');
		$this->object->validates_numericality_of('gallery_width');
		$this->object->validates_numericality_of('gallery_height');

		if ($this->object->settings['flash_enabled']) {
		}
	}
}
