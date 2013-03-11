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

		function save_action()
	{
		// Ensure that a lightbox library was selected
		if (($id = $this->object->param('lightbox_library_id'))) {
			$settings = $this->object->get_model();

			// Get the lightbox library mapper and find the library selected
			$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
			$library = $mapper->find($id, TRUE);

			// If a valid library, we have updated settings from the user, then
			// try saving the changes
			if ($library && (($params = $this->object->param('lightbox_library')))) {
				foreach ($params as $k=>$v) $library->$k = $v;
				$mapper->save($library);

				// If the requested changes weren't valid, add the validation
				// errors to the C_NextGen_Settings object
				if ($settings->is_invalid()) {
					foreach ($library->get_errors() as $property => $errs) {
						foreach ($errs as $error) $settings->add_error(
							$error, $property
						);;
					}
				}

				// The lightbox library update was successful.
				// Update C_NextGen_Settings
				else {
					$settings->thumbEffect = $library->name;
					$settings->thumbCode   = $library->code;
					$settings->save();
				}
			}
		}
	}
}