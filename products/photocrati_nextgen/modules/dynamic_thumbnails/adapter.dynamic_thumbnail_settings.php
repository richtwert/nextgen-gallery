<?php

class A_Dynamic_Thumbnail_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('dynamic_thumbnail_route', 'nextgen_image');
	}
}