<?php

/***
{
	Module:	photocrati-nextgen_settings
}
***/

class M_NextGen_Settings extends C_Base_Module
{
	var $activator = NULL;

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
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->activator = $this->_get_registry()->get_utility('I_NextGen_Activator');
	}


	/**
	 * Register utilities necessary for this module (and the plugin)
	 */
	function _register_utilities()
	{
		$this->_get_registry()->add_utility('I_NextGen_Settings', 'C_NextGen_Settings');
		$this->_get_registry()->add_utility('I_NextGen_Activator','C_NextGen_Activator');
	}

	
	/**
	 * Use the NextGEN Activator to run activation routines
	 */
	function _register_hooks()
	{
		add_action(
			'activate_'.PHOTOCRATI_GALLERY_PLUGIN_BASENAME,
			array(&$this->activator, 'install')
		);
	}
}

new M_NextGen_Settings();