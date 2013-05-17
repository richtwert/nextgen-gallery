<?php

/***
{
	Module:	photocrati-nextgen_settings
}
***/

class M_NextGen_Settings extends C_Base_Module
{
	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen_settings',
			'NextGEN Gallery Settings',
			'Provides central management for NextGEN Gallery settings',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Registers adapters required by this module
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Settings_Manager',
			'A_NextGen_Settings_Manager'
		);
	}

    function get_type_list()
    {
        return array(
            'A_Nextgen_Settings_Manager' => 'adapter.nextgen_settings_manager.php'
        );
    }
}

new M_NextGen_Settings();
