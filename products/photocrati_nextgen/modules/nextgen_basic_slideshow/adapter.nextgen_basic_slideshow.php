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
			$this->object->settings['cycle_effect'] = $settings->slideFx;

		if (!isset($this->object->settings['flash_enabled']))
			$this->object->settings['flash_enabled'] = $settings->enableIR;
		if (!isset($this->object->settings['flash_path']))
			$this->object->settings['flash_path'] = $settings->irURL;
		if (!isset($this->object->settings['flash_shuffle']))
			$this->object->settings['flash_shuffle'] = $settings->irShuffle;
        if (!isset($this->object->settings['flash_next_on_click']))
			$this->object->settings['flash_next_on_click'] = $settings->irLinkfromdisplay;
		if (!isset($this->object->settings['flash_navigation_bar']))
			$this->object->settings['flash_navigation_bar'] = $settings->irShownavigation;
		if (!isset($this->object->settings['flash_loading_icon']))
			$this->object->settings['flash_loading_icon'] = $settings->irShowicons;
		if (!isset($this->object->settings['flash_watermark_logo']))
			$this->object->settings['flash_watermark_logo'] = $settings->irWatermark;
		if (!isset($this->object->settings['flash_stretch_image']))
			$this->object->settings['flash_stretch_image'] = $settings->irOverstretch;
		if (!isset($this->object->settings['flash_transition_effect']))
			$this->object->settings['flash_transition_effect'] = $settings->irTransition;
		if (!isset($this->object->settings['flash_slow_zoom']))
			$this->object->settings['flash_slow_zoom'] = $settings->irKenburns;
		if (!isset($this->object->settings['flash_background_color']))
			$this->object->settings['flash_background_color'] = $settings->irBackcolor;
		if (!isset($this->object->settings['flash_text_color']))
			$this->object->settings['flash_text_color'] = $settings->irFrontcolor;
		if (!isset($this->object->settings['rollover_color']))
			$this->object->settings['flash_rollover_color'] = $settings->irLightcolor;
		if (!isset($this->object->settings['screen_color']))
			$this->object->settings['flash_screen_color'] = $settings->irScreencolor;
		if (!isset($this->object->settings['background_music']))
			$this->object->settings['flash_background_music'] = $settings->irAudio;
		if (!isset($this->object->settings['flash_xhtml_validation']))
			$this->object->settings['flash_xhtml_validation'] = $settings->irXHTMLvalid;
		if (!isset($this->object->settings['effects_code'])) {
			$this->object->settings['effect_code'] = $settings->thumbCode;
		}

		// Override defaults for alternative view settings
		if (!isset($this->object->settings['alternative_view_link_text'])) {
			$this->object->settings['alternative_view_link_text'] = _('[Show Picture List]');
		}
		if (!isset($this->object->settings['return_link_text'])) {
			$this->object->settings['return_link_text'] = _('[Show Slideshow]');
		}
	}

	function validation()
	{
		$this->object->validates_presence_of('gallery_width');
		$this->object->validates_presence_of('gallery_height');
		$this->object->validates_numericality_of('gallery_width');
		$this->object->validates_numericality_of('gallery_height');

		if ($this->object->settings['flash_enabled']) {
			//$this->object->validates_presence_of('flash_path');
		}
	}
}
