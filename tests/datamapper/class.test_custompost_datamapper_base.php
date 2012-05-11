<?php

include_once('class.test_datamapper_driver_base.php');
abstract class C_Test_CustomPost_DataMapper_Base extends C_Test_DataMapper_Driver_Base
{
		/**
	 * Setup test data
	 */
	function __construct($title=FALSE)
	{
		parent::__construct($title?$title:'Test Case for C_CustomPost_DataMapper_Driver');
	}


	/**
	 * Creates a datamapper for WordPress 'posts'
	 * @return C_CustomPost_DataMapper
	 */
	function setUp()
	{
		$retval = FALSE;

		// Create a valid data mapper for 'posts' using a factory
		$this->mapper = $this->get_factory()->create('custom_post_datamapper', $this->post_type);
		$this->assert_valid_datamapper($this->mapper);
		$this->assertIsA($this->mapper, 'C_CustomPost_DataMapper_Driver');

		// Create a valid data mapper for 'posts' without a factory
		// This is necessary for unit testing, as we want to test the
		// datamapper not the factory class"
		$this->mapper = new C_CustomPost_DataMapper_Driver($this->post_type);
		$this->assert_valid_datamapper($this->mapper);

		// For testing purposes, we'll add some mocking capabilities to the
		// mapper
		$this->mapper->add_mixin('Mock_Mixin_DataMapper_Driver');

		return $retval;
	}

	function log_queries($query)
	{
		error_log("EXECUTING: ".$query);
		return $query;
	}


	function test_custom_query_using_metadata()
	{
		// Create some test entries
		$retval = FALSE;
		$this->ids_to_cleanup[]= $retval = $this->mapper->save((object)array(
			'custom_value'	=> 'foobar',
			'custom_sort'	=> 'abc',
			'post_title'	=> 'A Title Test'
		));
		$this->assertTrue($retval > 0);
		
		$this->ids_to_cleanup[] = $retval = $this->mapper->save((object)array(
			'custom_value'	=> 'foobar',
			'custom_sort'	=> 'xyz',
			'post_title'	=> 'ZA Title Test'
		));
		$this->assertTrue($retval > 0);


		// Find the above results by searching for entities
		// by post metadata
		$results = $this->mapper->select()->where(array(
			array('post_title LIKE %s', "%A Title Test"),
			array('custom_value = %s', 'foobar')
		))->order_by('custom_sort', 'DESC')->run_query();
		$this->assertEqual(count($results), 2);
		if ($results) {
			$this->assertEqual($results[0]->post_title, 'ZA Title Test');
			$this->assertEqual($results[1]->post_title, 'A Title Test');
		}
	}


	/*************************************************************************
	**
	 * HELPER METHODS
	**
	**************************************************************************/

	/**
	 * Returns the expected primary key
	 * @return string
	 */
	function get_expected_primary_key()
	{
		return 'ID';
	}


	function assert_valid_entity($entity, $retval=FALSE, $test_retval=FALSE)
	{
		parent::assert_valid_entity($entity, $retval, $test_retval);
		$this->assertEqual($entity->post_type, $this->post_type);
		$this->assertTrue(isset($entity->post_author));
		$this->assertTrue(isset($entity->post_content_filtered));
		$this->assertTrue(isset($entity->post_date));
		$this->assertTrue(isset($entity->post_name));
	}
}