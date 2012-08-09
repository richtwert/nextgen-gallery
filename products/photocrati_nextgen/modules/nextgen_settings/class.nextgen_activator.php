<?php

class C_NextGen_Activator extends C_Component
{
	static $_instances = array();

	function define()
	{
		parent::define();
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
		// Ensures that ALL options are present
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');
		$settings->save();
	}
}