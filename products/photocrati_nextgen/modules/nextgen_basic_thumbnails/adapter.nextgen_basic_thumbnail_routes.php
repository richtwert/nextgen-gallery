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
		if (defined('NEXTGEN_GALLERY_BASIC_SLIDESHOW')) {
			$this->object->rewrite("nggallery{*}/slideshow/{*}", "nggallery{1}/show--".NEXTGEN_GALLERY_BASIC_SLIDESHOW."{2}");
			$this->object->rewrite("nggallery{*}/show--slide/{*}", "nggallery{1}/show--".NEXTGEN_GALLERY_BASIC_SLIDESHOW."/{2}");
			$this->object->rewrite("nggallery{*}/show--gallery/{*}", "nggallery{1}/show--".NEXTGEN_GALLERY_BASIC_THUMBNAILS."/{2}");
			$this->object->rewrite("nggallery{*}/page/{\d}{*}", "nggallery{1}/page--{2}{3}");
		}
	}
}