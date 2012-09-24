<?php

class A_NextGen_Basic_Slideshow_Mapper extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'set_defaults',
			get_class(),
			'Hook_NextGen_Basic_Slideshow_Defaults'
		);
	}
}

/**
 * Sets default values for the NextGen Basic Slideshow display type
 */
class Hook_NextGen_Basic_Slideshow_Defaults extends Hook
{
	function set_defaults($entity)
	{
		if ($entity->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW) {
			$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
			$this->object->_set_default_value($entity, 'settings', 'images_per_page', $settings->galImages);
			$this->object->_set_default_value($entity, 'settings', 'gallery_width', $settings->irWidth);
			$this->object->_set_default_value($entity, 'settings', 'gallery_height', $settings->irHeight);
			$this->object->_set_default_value($entity, 'settings', 'thumbnail_width', $settings->thumbwidth);
			$this->object->_set_default_value($entity, 'settings', 'thumbnail_height', $settings->thumbheight);
			$this->object->_set_default_value($entity, 'settings', 'cycle_interval', $settings->irRotateTime);
			$this->object->_set_default_value($entity, 'settings', 'cycle_effect', $settings->slideFx);
			$this->object->_set_default_value($entity, 'settings', 'flash_enabled', $settings->enableIR);
			$this->object->_set_default_value($entity, 'settings', 'flash_path', $settings->irURL);
			$this->object->_set_default_value($entity, 'settings', 'flash_shuffle', $settings->irShuffle);
			$this->object->_set_default_value($entity, 'settings', 'flash_next_on_click', $settings->irLinkfromdisplay);
			$this->object->_set_default_value($entity, 'settings', 'flash_navigation_bar', $settings->irShownavigation);
			$this->object->_set_default_value($entity, 'settings', 'flash_loading_icon', $settings->irShowicons);
			$this->object->_set_default_value($entity, 'settings', 'flash_watermark_logo', $settings->irWatermark);
			$this->object->_set_default_value($entity, 'settings', 'flash_stretch_image', $settings->irOverstretch);
			$this->object->_set_default_value($entity, 'settings', 'flash_transition_effect', $settings->irTransition);
			$this->object->_set_default_value($entity, 'settings', 'flash_slow_zoom', $settings->irKenburns);
			$this->object->_set_default_value($entity, 'settings', 'flash_background_color', $settings->irBackcolor);
			$this->object->_set_default_value($entity, 'settings', 'flash_text_color', $settings->irFrontcolor);
			$this->object->_set_default_value($entity, 'settings', 'flash_rollover_color', $settings->irLightcolor);
			$this->object->_set_default_value($entity, 'settings', 'flash_screen_color', $settings->irScreencolor);
			$this->object->_set_default_value($entity, 'settings', 'flash_background_music', $settings->irAudio);
			$this->object->_set_default_value($entity, 'settings', 'flash_xhtml_validation', $settings->irXHTMLvalid);
			$this->object->_set_default_value($entity, 'settings', 'effect_code', $settings->thumbCode);
			$this->object->_set_default_value($entity, 'settings', 'alternative_view_link_text', _('[Show Picture List]'));
			$this->object->_set_default_value($entity, 'settings', 'return_link_text', _('[Show Slideshow]'));
			$this->object->_set_default_value($entity, 'settings', 'show_alternative_view_link', PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS);
		}
	}
}