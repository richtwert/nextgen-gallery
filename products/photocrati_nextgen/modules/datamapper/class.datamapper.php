<?php

class Mixin_DataMapper extends Mixin
{
	/**
	 * Returns the name of the class which provides the datamapper
	 * implementation
	 * @return string
	 */
	function _get_datamapper_implementation_factory_method()
	{
		$factory_method = '';

		if (!defined('DATAMAPPER_IMPLEMENTATION')) {
			$factory_method = get_option(PHOTOCRATI_GALLERY_OPTION_PREFIX.'datamapper_implementation');
			define('DATAMAPPER_IMPLEMENTATION', $factory_method);
		}
		else $factory_method = DATAMAPPER_IMPLEMENTATION;

		$factory_method = 'custom_post_datamapper';

		return $factory_method;
	}
}

class C_DataMapper extends C_Component
{
	function define($object_name, $context=FALSE)
	{
		$this->add_mixin('Mixin_DataMapper');
		$this->wrap('I_DataMapper_Driver', array(&$this, '_get_datamapper_implementation'), array($object_name, $context));
	}

	/**
	 * Returns the implementation for the datamapper
	 * @param array $args
	 * @return mixed
	 */
	function _get_datamapper_implementation($args)
	{
		$object_name = $args[0];
		$context = $args[1];
		$method = $this->_get_datamapper_implementation_factory_method();
		$factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
		return $factory->create($method, $object_name, $context);
	}
}