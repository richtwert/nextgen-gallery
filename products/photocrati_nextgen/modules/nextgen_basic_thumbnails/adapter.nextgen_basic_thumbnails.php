<?php

class A_NextGen_Basic_Thumbnails extends Mixin
{
	function initialize()
	{
		if ($this->object->name == NEXTGEN_GALLERY_NEXTGEN_BASIC_THUMBNAILS) {
			$this->object->add_pre_hook(
				'validation',
				get_class(),
				'Hook_NextGen_Basic_Thumbnails_Validation'
			);
		}
	}
}

class Hook_NextGen_Basic_Thumbnails_Validation extends Hook
{
	function validation()
	{
		$this->object->validates_presence_of('thumbnail_width');
		$this->object->validates_presence_of('thumbnail_height');
		$this->object->validates_numericality_of('thumbnail_width');
		$this->object->validates_numericality_of('thumbnail_height');
		$this->object->validates_numericality_of('images_per_page');
	}
}
