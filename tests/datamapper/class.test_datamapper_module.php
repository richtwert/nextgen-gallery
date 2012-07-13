<?php

class C_Test_DataMapper_Module extends UnitTestCase
{
	/**
	 * @var C_Component Registry
	 */
	private $_registry = NULL;


	function setUp()
	{
		$this->_registry = C_Component_Registry::get_instance();
	}

	function test_whether_module_is_loaded()
	{
		$this->assertNotNull($this->_registry->get_module('photocrati-datamapper'));
	}

	function test_whether_factory_methods_exist()
	{
		$factory = $this->_registry->get_singleton_utility('I_Component_Factory');
		$this->assertTrue($factory->has_method('datamapper'));
		$this->assertTrue($factory->has_method('datamapper_model'));
		$this->assertTrue($factory->has_method('custom_table_datamapper'));
		$this->assertTrue($factory->has_method('custom_post_datamapper'));
	}
}