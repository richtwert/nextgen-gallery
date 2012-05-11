<?php
include_once('class.test_custompost_datamapper_base.php');

//SimpleTest::ignore('C_Test_DataMapper_Using_CustomPost_Driver');

/**
 * Provides a test case for the C_DataMapper class using the CustomPost driver
 */
class C_Test_DataMapper_Using_CustomPost_Driver extends C_Test_CustomPost_DataMapper_Base
{
	function __construct()
	{
		parent::__construct("Test Case for C_DataMapper using CustomPost Driver");
	}

	/**
	 * Creates a datamapper for WordPress 'posts'
	 * @return C_CustomPost_DataMapper
	 */
	function setUp()
	{
		$retval = FALSE;

		// Tell the datamapper which driver to use
		update_option(PHOTOCRATI_GALLERY_OPTION_PREFIX.'datamapper_driver', 'custom_post_datamapper');

		// Create a valid data mapper for 'posts' using a factory
		$this->mapper = $this->get_factory()->create('datamapper', $this->post_type, 'SIMPLE_TEST');
		$this->assert_valid_datamapper($this->mapper);
		$this->assertIsA($this->mapper, 'C_DataMapper');

		// Create a valid data mapper for 'posts' without a factory
		// This is necessary for unit testing, as we want to test the
		// datamapper not the factory class"
		$this->mapper = new C_DataMapper($this->post_type, 'SIMPLE_TEST');
		$this->assert_valid_datamapper($this->mapper);

		// For testing purposes, we'll add some mocking capabilities to the
		// mapper
		$this->mapper->add_mixin('Mock_Mixin_DataMapper_Driver');
		$this->mapper->_wrapped_class->add_mixin('Mock_Mixin_DataMapper_Driver');

		return $retval;
	}
}