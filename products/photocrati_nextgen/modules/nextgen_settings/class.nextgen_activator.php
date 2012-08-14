<?php

class C_NextGen_Activator extends C_Component
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Activator');
		$this->implement('I_NextGen_Activator');
	}

	/**
	 * Gets the class instance
	 * @param string|array|FALSE $context
	 * @return C_NextGen_Activator
	 */
	static function get_instance($context=FALSE)
	{
		$klass = get_class();
		if (!isset(self::$_instances[$context])) {
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provides the install function, which other modules can provide hooks for to
 * run activation routines
 */
class Mixin_NextGen_Activator extends Mixin
{
	function install()
	{
		// We need a custom lightbox library option
		$mapper = $this->object->_get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$mapper->save((object)array(
			'name'	=>	'Custom',
		));
		$mapper->save((object)array(
			'name'				=>	'Test',
			'code'				=>	'class="foobar"',
			'css_stylesheets'	=>	'http://www.google.ca/style.css',
			'scripts'			=>	'http://www.google.ca/script.js'
		));

		// Install multisite options
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings', 'multisite');
		$settings->save();

		// Install blog specific options
		// TODO: Need to determine if this was network activated, and if so, to
		// install blog specific options for every blog
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');
		$settings->save();
	}
}