<?php

class A_NextGen_Basic_Thumbnails_Mapper extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'set_defaults',
			'NextGen Basic Thumbnails Defaults',
			'Hook_NextGen_Basic_Thumbnails_Defaults'
		);
	}
}

class Hook_NextGen_Basic_Thumbnails_Defaults extends Hook
{
	function set_defaults($entity)
	{
		if ($entity->name == NEXTGEN_GALLERY_BASIC_THUMBNAILS) {
			$settings = $this->object->get_registry()->get_utility('I_Settings_Manager');
			$this->object->_set_default_value($entity, 'settings', 'images_per_page', $settings->galImages);
			$this->object->_set_default_value($entity, 'settings', 'number_of_columns', $settings->galColumns);
			$this->object->_set_default_value($entity, 'settings', 'thumbnail_width', $settings->thumbwidth);
			$this->object->_set_default_value($entity, 'settings', 'thumbnail_height', $settings->thumbheight);
			$this->object->_set_default_value($entity, 'settings', 'show_all_in_lightbox', $settings->galHiddenImg);
			$this->object->_set_default_value($entity, 'settings', 'ajax_pagination', $settings->galAjaxNav);
            $this->object->_set_default_value($entity, 'settings', 'template', '');

			// TODO: Should this be called enable pagination?
			$this->object->_set_default_value($entity, 'settings', 'disable_pagination', 0);

			// Alternative view support
			$this->object->_set_default_value($entity, 'settings', 'show_alternative_view_link', $settings->galShowSlide ? 1 : 0);
			$this->object->_set_default_value($entity, 'settings', 'alternative_view', NEXTGEN_GALLERY_BASIC_SLIDESHOW);
			$this->object->_set_default_value($entity, 'settings', 'alternative_view_link_text', $settings->galTextSlide);
			$this->object->_set_default_value($entity, 'settings', 'show_return_link', 1);
			$this->object->_set_default_value($entity, 'settings', 'return_link_text', $settings->galTextGallery);

            // override thumbnail settings
            $this->object->_set_default_value($entity, 'settings', 'override_thumbnail_settings', 0);
            $this->object->_set_default_value($entity, 'settings', 'thumbnail_quality', '100');
            $this->object->_set_default_value($entity, 'settings', 'thumbnail_crop', 0);
            $this->object->_set_default_value($entity, 'settings', 'thumbnail_watermark', 0);

			// Show piclens link ?
			$this->object->_set_default_value($entity, 'settings', 'piclens_link_text', _('[Show PicLens]'));
			$this->object->_set_default_value($entity, 'settings', 'show_piclens_link',
				isset($entity->settings['show_piclens_link']) &&
				  preg_match("/^true|yes|y$/", $entity->settings['show_piclens_link']) ?
					1 : 0
			);
		}
	}
}
