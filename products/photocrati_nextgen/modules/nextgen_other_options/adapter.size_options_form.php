<?php

class A_Size_Options_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Size Options';
	}

	function render()
	{
		$settings = $this->object->get_model();
		
		return $this->render_partial('nextgen_other_options#size_options_tab', array(
			'size_list_label'		=>	_('Size List'),
			'size_list_help'		=>	_('List of default sizes used for thumbnails and images'),
			'size_list'		=>	$settings->thumbnail_dimensions
		), TRUE);
	}

	function save_action()
	{	
		if (($settings = $this->object->param('size_settings'))) {
			$this->object->get_model()->set($settings)->save();
		}
	}
}
