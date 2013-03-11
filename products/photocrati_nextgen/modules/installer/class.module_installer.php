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

class C_Module_Installer extends C_Component
{
	static $_instances = array();
	var $global_settings;
	var $settings;

	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Settings_Installer');
		$this->implement('I_Installer');
	}

	function initialize()
	{
		parent::initialize();
		$this->global_settings	= $this->get_registry()->get_utility(
			'I_Settings_Manager',
			'global'
		);

		$this->settings			= $this->get_registry()->get_utility(
			'I_Settings_Manager'
		);
	}
}