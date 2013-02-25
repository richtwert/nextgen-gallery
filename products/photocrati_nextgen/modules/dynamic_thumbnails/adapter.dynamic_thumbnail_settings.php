<?php

class A_Dynamic_Thumbnail_Settings extends Mixin
{
	function initialize()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		$settings->dynamic_thumbnail_route = 'nextgen_image';
	}
}