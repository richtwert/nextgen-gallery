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
		$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$mapper->save((object)array(
			'name'	=>	'Custom',
		));

		// Install our options
		$settings = $this->object->get_registry()->get_utility('I_Settings_Manager');
		$settings->save();

        if ($settings->is_multisite())
        {
            $this->object
                 ->get_registry()
                 ->get_utility('I_Settings_Manager', 'multisite')
                 ->save();
        }
	}
}
