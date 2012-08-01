<?php

class DataMapperDriverNotSelectedException extends Exception
{

}

class Mixin_DataMapper extends Mixin
{
	/**
	 * Returns the name of the class which provides the datamapper
	 * implementation
	 * @return string
	 */
	function _get_driver_factory_method($context=FALSE)
	{
		$factory_method = '';

		// No constant has been defined to establish a global datamapper driver
		if (!defined('DATAMAPPER_DRIVER')) {

			// Get the datamapper configured in the database
			$settings = $this->object->_get_registry()->get_singleton_utility('I_NextGen_Settings');
			$factory_method = $settings->datamapper_driver;
			if (!$factory_method) throw new DataMapperDriverNotSelectedException();

			// Define a constant and use this as the global datamapper driver,
			// unless running in a SimpleTest Environment
			if (!isset($GLOBALS['SIMPLE_TEST_RUNNING']))
				define('DATAMAPPER_DRIVER', $factory_method);
		}

		// Use the globally defined datamapper driver in the constant
		else $factory_method = DATAMAPPER_DRIVER;

		return $factory_method;
	}
}

class C_DataMapper extends C_Component
{
	function define($object_name, $context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_DataMapper');
		$this->wrap('I_DataMapper_Driver', array(&$this, '_get_driver'), array($object_name, $context));
	}

	/**
	 * Returns the implementation for the datamapper
	 * @param array $args
	 * @return mixed
	 */
	function _get_driver($args)
	{
		$object_name = $args[0];
		$context = $args[1];
		$method = $this->_get_driver_factory_method($context);
		$factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
		return $factory->create($method, $object_name, $context);
	}
}