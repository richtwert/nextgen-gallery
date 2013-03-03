<?php

class A_Thumbnail_Options_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Thumbnail Options';
	}

	function render()
	{
		$settings = $this->object->get_model();
		return $this->render_partial('nextgen_other_options#thumbnail_options_tab', array(
			'thumbnail_dimensions_label'		=>	_('Default thumbnail dimensions:'),
			'thumbnail_dimensions_help'		=>	_('When generating thumbnails, what image dimensions do you desire?'),
			'thumbnail_dimensions_width'		=>	$settings->thumbwidth,
			'thumbnail_dimensions_height'		=>	$settings->thumbheight,
			'thumbnail_quality_label'		=>	_('Adjust Thumbnail Quality?'),
			'thumbnail_quality_help'		=>	_('When generating thumbnails, what image quality do you desire?'),
			'thumbnail_quality'				=>	$settings->thumbquality
		), TRUE);
	}
}