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
		$router = $this->object->get_registry()->get_utility('I_Router');
		$router->rewrite("/nggallery{*}/slideshow", "/nggallery{1}/show--photocrati-nextgen_basic_slideshow/");
	}
}