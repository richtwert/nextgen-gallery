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

		if (!defined('DATAMAPPER_DRIVER')) {
			$factory_method = get_option(PHOTOCRATI_GALLERY_OPTION_PREFIX.'datamapper_driver');
			if (!$factory_method) throw new DataMapperDriverNotSelectedException();
			if ($context) {
				if (!is_array($context)) $context=array($context);
				if (!in_array('SIMPLE_TEST', $context)) define('DATAMAPPER_DRIVER', $factory_method);
			}
		}
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