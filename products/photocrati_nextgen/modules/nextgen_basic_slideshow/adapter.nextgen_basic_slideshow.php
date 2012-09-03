<?php

class A_NextGen_Basic_Slideshow extends Mixin
{
	function initialize()
	{
		if ($this->object->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW) {
			$this->object->add_pre_hook(
				'validation',
				get_class(),
				'Hook_NextGen_Basic_Slideshow_Validation'
			);
			
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
	function set_defaults()
	{
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		// Set defaults
		if (!isset($this->object->settings))
			$this->object->settings = array();
		if (!isset($this->object->settings['template']))
			$this->object->settings['template'] = '';
		if (!isset($this->object->settings['images_per_page']))
			$this->object->settings['images_per_page'] = $settings->galImages;
		if (!isset($this->object->settings['gallery_width']))
			$this->object->settings['gallery_width'] = $settings->irWidth;
		if (!isset($this->object->settings['gallery_height']))
			$this->object->settings['gallery_height'] = $settings->irHeight;
		if (!isset($this->object->settings['thumbnail_width']))
			$this->object->settings['thumbnail_width'] = $settings->thumbwidth;
		if (!isset($this->object->settings['thumbnail_height']))
			$this->object->settings['thumbnail_height'] = $settings->thumbheight;
		if (!isset($this->object->settings['cycle_interval']))
			$this->object->settings['cycle_interval'] = $settings->irRotatetime;
		if (!isset($this->object->settings['cycle_effect']))
			$this->object->settings['cycle_effect'] = $settings->irTransition;
		if (!isset($this->object->settings['effects_code'])) {
			$this->object->settings['effect_code'] = $settings->thumbCode;
		}
	}

	function validation()
	{
		$this->object->validates_presence_of('gallery_width');
		$this->object->validates_presence_of('gallery_height');
		$this->object->validates_numericality_of('gallery_width');
		$this->object->validates_numericality_of('gallery_height');
		
		$this->object->validates_presence_of('thumbnail_width');
		$this->object->validates_presence_of('thumbnail_height');
		$this->object->validates_numericality_of('thumbnail_width');
		$this->object->validates_numericality_of('thumbnail_height');
		$this->object->validates_numericality_of('images_per_page');
	}
}
