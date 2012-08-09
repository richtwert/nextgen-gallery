<?php

class A_NextGen_Basic_Thumbnails extends Mixin
{
	function initialize()
	{
		if ($this->object->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS) {
			$this->object->add_pre_hook(
				'validate',
				get_class(),
				'Hook_NextGen_Basic_Thumbnails_Validation'
			);
			$this->object->add_pre_hook(
				'set_defaults',
				get_class(),
				'Hook_NextGen_Basic_Thumbnails_Validation'
			);
		}
	}
}

class Hook_NextGen_Basic_Thumbnails_Validation extends Hook
{
	function set_defaults()
	{
		// Set defaults
		if (!isset($this->object->settings))
			$this->object->settings = array();
		if (!isset($this->object->settings['template']))
			$this->object->settings['template'] = '';
		if (!isset($this->object->settings['images_per_page']))
			$this->object->settings['images_per_page'] = FALSE;
		if (!isset($this->object->settings['slideshow_link_text'])) {
			$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');
			$this->object->settings['slideshow_link_text'] = $settings->galTextGallery;
		}
	}

	function validate()
	{
		$this->object->validates_numericality_of('images_per_page');
	}
}