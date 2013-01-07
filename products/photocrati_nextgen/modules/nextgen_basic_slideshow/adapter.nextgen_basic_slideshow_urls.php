<?php

class A_NextGen_Basic_Slideshow_Urls extends Mixin
{
	function create_parameter_segment($key, $value, $id, $use_prefix)
	{
		if ($key == 'show' && $value == NEXTGEN_GALLERY_BASIC_SLIDESHOW)
			return '/slideshow/';
		else
			return $this->call_parent('create_parameter_segment', $key, $value, $id, $use_prefix);
		
	}
}