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
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		if (!isset($settings->datamapper_driver)) throw new DataMapperDriverNotSelectedException();
		return $settings->datamapper_driver;
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
		$factory = $this->get_registry()->get_utility('I_Component_Factory');
		return $factory->create($method, $object_name, $context);
	}
}
