<?php

class Mixin_Settings_Installer extends Mixin
{
	function install()
	{
		$this->object->global_settings->save();
		$this->object->settings->save();
	}

	function uninstall()
	{
		$this->object->global_settings->destroy();
		$this->object->settings->destroy();
	}
}

class C_Installer extends C_Component
{
	var $global_settings;
	var $settings;

	function define($context=FALSE)
	{
		parent::define($context);
		$this->implement('I_Installer');
	}

	function initialize()
	{
		$this->global_settings	= $this->get_registry()->get_utility(
			'I_Settings_Manager',
			'global'
		);

		$this->settings			= $this->get_registry()->get_utility(
			'I_Settings_Manager'
		);
	}
}