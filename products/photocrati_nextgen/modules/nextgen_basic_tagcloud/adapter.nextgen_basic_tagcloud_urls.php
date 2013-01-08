<?php

class A_NextGen_Basic_TagCloud_Urls extends Mixin
{
	function create_parameter_segment($key, $value, $id, $use_prefix)
	{
		if ($key == 'gallerytag') {
			return 'tags/'.$value;
		}
		else return $this->call_parent('create_parameter_segment', $key, $value, $id, $use_prefix)
;	}
}