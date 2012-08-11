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
		if (!isset($this->object->settings['piclens_link_text']))
			$this->object->settings['piclens_link_text'] = 'Show PicLens';
		if (!isset($this->object->settings['number_of_columns']))
			$this->object->settings['number_of_columns'] = $settings->galColumns;
		if (!isset($this->object->settings['thumbnail_width']))
			$this->object->settings['thumbnail_width'] = $settings->thumbwidth;
		if (!isset($this->object->settings['thumbnail_height']))
			$this->object->settings['thumbnail_height'] = $settings->thumbheight;

		// Show slideshow link ?
		if (!isset($this->object->settings['show_slideshow_link']))
			$this->object->settings['show_slideshow_link'] = $settings->galShowSlide;
		elseif (is_string($this->object->settings['show_slideshow_link'])) {
			if (preg_match("/^1|true|yes$/", $this->object->settings['show_slideshow_link']))
				$this->object->settings['show_slideshow_link'] = TRUE;
			else
				$this->object->settings['show_slideshow_link'] = FALSE;
		}

		// Show piclens link?
		if (!isset($this->object->settings['show_piclens_link']))
			$this->object->settings['show_piclens_link'] = $settings->usePicLens;
		elseif (is_string($this->object->settings['show_piclens_link'])) {
			if (preg_match("/^1|true|yes$/", $this->object->settings['show_piclens_link']))
				$this->object->settings['show_piclens_link'] = TRUE;
			else
				$this->object->settings['show_piclens_link'] = FALSE;
		}
	}

	function validate()
	{
		$this->object->validates_presence_of('thumbnail_width');
		$this->object->validates_presence_of('thumbnail_height');
		$this->object->validates_numericality_of('thumbnail_width');
		$this->object->validates_numericality_of('thumbnail_height');
		$this->object->validates_numericality_of('images_per_page');
	}
}