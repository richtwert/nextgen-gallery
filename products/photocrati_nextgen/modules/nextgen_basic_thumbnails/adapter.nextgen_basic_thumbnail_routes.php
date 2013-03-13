<?php

class A_NextGen_Basic_Thumbnail_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Routes for NextGen Basic Thumbnails',
			get_class(),
			'_add_nextgen_basic_thumbnail_routes'
		);
	}

	function _add_nextgen_basic_thumbnail_routes()
	{
		if (defined('NEXTGEN_GALLERY_BASIC_SLIDESHOW'))
        {
            $slug = $this->object->get_registry()->get_utility('I_Settings_Manager')->router_param_slug;
			$this->object->rewrite("{$slug}{*}/slideshow/{*}",     "{$slug}{1}/show--" . NEXTGEN_GALLERY_BASIC_SLIDESHOW  . "{2}");
			$this->object->rewrite("{$slug}{*}/show--slide/{*}",   "{$slug}{1}/show--" . NEXTGEN_GALLERY_BASIC_SLIDESHOW  . "/{2}");
			$this->object->rewrite("{$slug}{*}/show--gallery/{*}", "{$slug}{1}/show--" . NEXTGEN_GALLERY_BASIC_THUMBNAILS . "/{2}");
			$this->object->rewrite("{$slug}{*}/page/{\\d}{*}",     "{$slug}{1}/page--{2}{3}");
		}
	}
}