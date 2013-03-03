<?php

class A_Lightbox_Library_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Lightbox Effects';
	}

	function render()
	{
		$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$libs = $mapper->find_all();

		// Render tab
		return $this->render_partial('nextgen_other_options#lightbox_library_tab', array(
			'lightbox_library_label'	=>	_('What effect would you like to use?'),
			'libs'						=>	$libs,
			'id_field'					=>	$mapper->get_primary_key_column(),
			'selected'					=>	$this->object->get_model()->thumbEffect,
		), TRUE);
	}
}