<?php
require_once('class.test_customtable_datamapper_base.php');

//SimpleTest::ignore('C_Test_DataMapper_Using_CustomTable_Driver');

/**
 * Provides a test case for the C_DataMapper class using the CustomTable driver
 */
class C_Test_DataMapper_Using_CustomTable_Driver extends C_Test_CustomTable_DataMapper_Base
{
	function __construct($label="Test Case for C_DataMapper using CustomTable Driver")
	{
		parent::__construct($label);

		// Tell the datamapper which driver to use
		$settings = $this->get_registry()->get_singleton_utility('I_NextGen_Settings');
		$settings->datamapper_driver = 'custom_table_datamapper';
		$settings->save();
	}

	/**
	 * Creates a datamapper for WordPress 'posts'
	 * @return C_CustomPost_DataMapper
	 */
	function setUp()
	{
		$retval = FALSE;

		// Create a valid data mapper for 'posts' using a factory
		$this->mapper = $this->get_factory()->create('datamapper', $this->post_type);
		$this->assert_valid_datamapper($this->mapper);
		$this->assertIsA($this->mapper, 'C_DataMapper');

		// Create a valid data mapper for 'posts' without a factory
		// This is necessary for unit testing, as we want to test the
		// datamapper not the factory class"
		$this->mapper = new C_DataMapper($this->post_type);
		$this->assert_valid_datamapper($this->mapper);

		// For testing purposes, we'll add some mocking capabilities to the
		// mapper
		$this->mapper->add_mixin('Mock_Mixin_DataMapper_Driver');
		$this->mapper->_wrapped_class->add_mixin('Mock_Mixin_DataMapper_Driver');

		return $retval;
	}
}