<?php

class Mixin_NextGen_Basic_Thumbnail_Urls extends Mixin
{
	function create_parameter_segment($key, $value, $id, $use_prefix)
	{
		if ($key == 'page')
			return '/page/'.$value;
		else
			return $this->call_parent('create_parameter_segment', $key, $value, $id, $use_prefix);
	}
}