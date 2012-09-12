<?php

class A_NextGen_Basic_Thumbnails extends Mixin
{
	function initialize()
	{
		if ($this->object->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS) {
			$this->object->add_pre_hook(
				'validation',
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
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		// Set defaults
		if (!isset($this->object->settings)) $this->object->settings = array();
		if (!isset($this->object->settings['template']))
			$this->object->settings['template'] = '';
		if (!isset($this->object->settings['images_per_page']))
			$this->object->settings['images_per_page'] = $settings->galImages;
		if (!isset($this->object->settings['alternative_view_link_text']))
			$this->object->settings['alternative_view_link_text'] = $settings->galTextSlide;
		if (!isset($this->object->settings['piclens_link_text']))
			$this->object->settings['piclens_link_text'] = 'Show PicLens';
		if (!isset($this->object->settings['number_of_columns']))
			$this->object->settings['number_of_columns'] = $settings->galColumns;
		if (!isset($this->object->settings['thumbnail_width']))
			$this->object->settings['thumbnail_width'] = $settings->thumbwidth;
		if (!isset($this->object->settings['thumbnail_height']))
			$this->object->settings['thumbnail_height'] = $settings->thumbheight;
        if (!isset($this->object->settings['show_all_in_lightbox']))
            $this->object->settings['show_all_in_lightbox'] = $settings->galHiddenImg;
        if (!isset($this->object->settings['ajax_pagination']))
            $this->object->settings['ajax_pagination'] = $settings->galAjaxNav;
        if (!isset($this->object->settings['disable_pagination']))
            $this->object->settings['disable_pagination'] = FALSE;

		// Show slideshow link ?
		if (!isset($this->object->settings['show_alternative_view_link']))
			$this->object->settings['show_alternative_view_link'] = $settings->galShowSlide;
		if (!isset($this->object->settings['show_return_link']))
			$this->object->settings['show_return_link'] = TRUE;
		if (!isset($this->object->settings['return_link_text']))
			$this->object->settings['return_link_text'] = $settings->galTextGallery;

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

	function validation()
	{
		$this->object->validates_presence_of('thumbnail_width');
		$this->object->validates_presence_of('thumbnail_height');
		$this->object->validates_numericality_of('thumbnail_width');
		$this->object->validates_numericality_of('thumbnail_height');
		$this->object->validates_numericality_of('images_per_page');
	}
}
