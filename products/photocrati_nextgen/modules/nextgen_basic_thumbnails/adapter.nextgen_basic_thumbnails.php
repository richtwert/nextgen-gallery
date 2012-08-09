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
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');

		// Set defaults
		if (!isset($this->object->settings))
			$this->object->settings = array();
		if (!isset($this->object->settings['template']))
			$this->object->settings['template'] = '';
		if (!isset($this->object->settings['images_per_page']))
			$this->object->settings['images_per_page'] = $settings->galImages;
		if (!isset($this->object->settings['slideshow_link_text']))
			$this->object->settings['slideshow_link_text'] = $settings->galTextSlide;
		if (!isset($this->object->settings['show_slideshow_link']))
			$this->object->settings['show_slideshow_link'] = $settings->galShowSlide;
		if (!isset($this->object->settings['show_piclens_link']))
			$this->object->settings['show_piclens_link'] = $settings->usePicLens;
		if (!isset($this->object->settings['piclens_link_text']))
			$this->object->settings['piclens_link_text'] = 'Show PicLens';
		if (!isset($this->object->settings['number_of_columns']))
			$this->object->settings['number_of_columns'] = $settings->galColumns;
		if (!isset($this->object->settings['thumb_width']))
			$this->object->settings['thumb_width'] = $settings->thumbwidth;
		if (!isset($this->object->settings['thumb_height']))
			$this->object->settings['thumb_height'] = $settings->thumbheight;
	}

	function validate()
	{
		$this->object->validates_numericality_of('images_per_page');
	}
}