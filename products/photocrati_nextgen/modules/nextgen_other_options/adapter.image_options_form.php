<?php

class A_Image_Options_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Image Options';
	}
}