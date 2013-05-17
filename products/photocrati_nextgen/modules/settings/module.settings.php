<?php
/*
{
	Module: photocrati-settings
}
 */
class M_Settings extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-settings',
			'Settings',
			'Provides a settings manager for modules to use',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
        $this->get_registry()->add_utility('I_Settings_Manager', 'C_Settings_Manager');
	}

	function _register_hooks()
	{
		add_filter('pre_update_option_', array(&$this, 'save_options_array'), 1, 3);
	}

	/**
	 * Ensures that when the C_Settings_Manager is passed to the update_option()
	 * method that the internal options array is used
	 * @param string $option_name
	 * @param mixed $new_value
	 * @param mixed $oldvalue
	 * @return mixed
	 */
	function save_options_array($option_name, $new_value, $oldvalue)
	{
		if (is_object($new_value) && get_class($new_value) == 'C_Settings_Manager') {
			$new_value = $new_value->_options;
		}

		return $new_value;
	}

    function get_type_list()
    {
        return array(
            'C_Settings_Manager' => 'class.settings_manager.php',
            'I_Settings_Manager' => 'interface.settings_manager.php'
        );
    }
}

new M_Settings;