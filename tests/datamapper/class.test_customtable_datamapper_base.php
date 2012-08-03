<?php
require_once('class.test_datamapper_driver_base.php');

/**
 * Provides a base class for test cases attempting to test the CustomTable
 * driver
 */
abstract class C_Test_CustomTable_DataMapper_Base extends C_Test_DataMapper_Driver_Base
{
	public $table_name;

	function __construct($title=FALSE)
	{
		parent::__construct($title?$title:"Test Case for C_CustomTable_DataMapper_Driver");
		$this->table_name = 'posts';
	}

	function setUp()
	{
		$retval = FALSE;

		// Create a valid data mapper for 'posts' using a factory
		$this->mapper = $this->get_factory()->create('custom_table_datamapper', $this->table_name);
		$this->assert_valid_datamapper($this->mapper);

		// Create a valid data mapper for 'posts' without a factory
		// This is necessary for unit testing, as we want to test the
		// datamapper not the factory class"
		$this->mapper = new C_CustomTable_DataMapper_Driver($this->table_name);
		$this->assert_valid_datamapper($this->mapper);

		// For testing purposes, we'll add some mocking capabilities to the
		// mapper
		$this->mapper->add_mixin('Mock_Mixin_DataMapper_Driver');

		return $retval;
	}

	/**
	 * Returns the expected primary key
	 * @return string
	 */
	function get_expected_primary_key()
	{
		return 'ID';
	}
}