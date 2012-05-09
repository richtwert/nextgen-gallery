<?php

/**
 * Tests the C_CustomPost_DataMapper_Driver
 */
class C_Test_CustomPost_DataMapper_Driver extends UnitTestCase
{
	/**
	 * Before each test, we insert a temporary new post into WordPress to
	 * test with
	 */
	function setUp()
	{
		$this->post_title = "Mike's Test Post";
		$this->post_type = 'posts';
		$this->model_factory_method = 'my_model';
		$this->post_id = wp_insert_post(array('post_title' => $this->post_title, 'post_type' => $this->post_type));
		if ($this->post_id === 0) $this->fail("Could not create temporary post to work with");
	}

	/**
	 * After each test, delete the temporary WordPress post
	 */
	function tearDown()
	{
		@wp_delete_post($this->post_id, TRUE);
	}

	/**
	 * Creates a datamapper for WordPress 'posts'
	 * @return C_CustomPost_DataMapper
	 */
	function test_create_new_datamapper()
	{
		$retval = FALSE;

		// Create a valid data mapper for 'posts' using a factory
		$mapper = $this->get_factory()->create('custom_post_datamapper', $this->post_type);
		$this->assert_valid_datamapper($mapper);

		// Create a valid data mapper for 'posts' without a factory
		// This is necessary for unit testing, as we want to test the
		// datamapper not the factory class"
		$retval = $mapper = new C_CustomPost_DataMapper_Driver($this->post_type);
		$this->assert_valid_datamapper($mapper);

		// For testing purposes, we'll add some mocking capabilities to the
		// mapper
		$mapper->add_mixin('Mock_Mixin_DataMapper_Driver');

		return $retval;
	}


	/**
	 * Tests creating new entities using the data mapper
	 */
	function test_create_entities()
	{
		$mapper = $this->test_create_new_datamapper();

		// You can create a new entity by using the save() method
		// of the data mapper.
		//
		// You can pass a stdClass object to the save method
		// The save method will always return the ID of the new entity
		// or FALSE if there was a problem
		$entity = (object) array('post_title' => $this->post_title);
		$result = $mapper->save($entity);
		$this->assert_valid_entity($entity, $result, TRUE);
	}


	/**
	 * When a datamapper returns individual entities, it returns them as stdClass
	 * instances. However, a 'model' class can be associated with the mapper to
	 * return instances of that class instead. This is useful when you wish to
	 * to encapsulate business logic, such as validation, for individual entities
	 *
	 * To associate a model class with a datamapper, you must inform the mapper
	 * which factory method to call when instantiating a model/entity.
	 */
	function test_model_factory_methods()
	{
		$mapper = $this->test_create_new_datamapper();

		// By default, datamappers are not associated with any model class
		$this->assertEqual(FALSE, $mapper->get_model_factory_method());

		// Set a fake factory method
		$mapper->set_model_factory_method($this->model_factory_method);
		$this->assertEqual(
			$this->model_factory_method,
			$mapper->get_model_factory_method()
		);

		return $mapper;
	}

	/**
	 * Find an individual post
	 */
	function test_find_methods()
	{
		// Create a new data mapper for posts.
		$mapper = $this->test_model_factory_methods();

		// To find an existing post, just use the find() method and pass the
		// ID of the record you wish to retrieve
		$entity = $mapper->find($this->post_id);
		$this->assertIsA($entity, 'stdClass');
		$this->assert_valid_entity($entity);

		// You may also optionally ask for a model class to be returned
		// See @test_model_factory_methods()
		$entity = $mapper->find($this->post_id, TRUE);
		$this->assertIsA($entity, 'C_DataMapper_Model');
		$this->assert_valid_entity($entity);

		// When attempting to find a record that doesn't exist, NULL will be
		// returned
		$entity = $mapper->find(PHP_INT_MAX); // highly unlikely that this exists
		$this->assertNull($entity);

		// You can also try finding a post based on other conditions
		$entity = $mapper->find_first(array('post_title IN (%s)', $this->post_title));
		$this->assertIsA($entity, 'stdClass');
		$this->assertEqual($entity->post_title, $this->post_title);

		// Like the find() method, find_first() will return NULL if no record
		// is found
		$entity = $mapper->find_first(array('post_title = %s', $this->random_string()));
		$this->assertNull($entity);

		// Like the find() method, find_first() can also return a model
		$entity = $mapper->find_first(array('post_title IN (%s)', $this->post_title),TRUE);
		$this->assertIsA($entity, 'C_DataMapper_Model');
		$this->assertEqual($entity->post_title, $this->post_title);
	}


	/*************************************************************************
	**
	 * HELPER METHODS
	 *
	**************************************************************************/

	/**
	 * Compares an entity with our test post created in the setUp() method
	 * @param stdClass $entity
	 */
	function assert_valid_entity($entity, $retval=FALSE, $test_retval=FALSE)
	{
		$this->assertEqual($entity->id_field, 'ID');
		if ($test_retval) {
			$this->assertTrue((is_int($retval) && $retval>0), "save() did not return a valid record ID");
			$this->assertEqual($retval, $entity->ID);
		}
		$this->assertTrue((is_int($entity->ID) && $entity->ID>0), "save() did not return a valid record ID");
		$this->assertEqual($entity->post_type, $this->post_type);
		$this->assertEqual($entity->post_title, $this->post_title);
	}

	/**
	 * Asserts that a datamapper is valid
	 * @global string $table_prefix
	 * @param C_CustomPost_DataMapper $mapper
	 */
	function assert_valid_datamapper($mapper)
	{
		global $table_prefix;
		$this->assertIsA($mapper, 'C_CustomPost_DataMapper_Driver');
		$this->assertEqual($mapper->get_primary_key_column(), 'ID');
		$this->assertEqual($mapper->get_table_name(), $table_prefix.'posts');
		$this->assertEqual($mapper->get_object_name(), $this->post_type);
	}


	/**
	 * Provides a convenience method for getting a factory object
	 * @return C_Component_Factory
	 */
	function get_factory()
	{
		$registry = C_Component_Registry::get_instance();
		return $registry->get_singleton_utility('I_Component_Factory');
	}


	/**
	 * Returns a random string of a particular length
	 * @param int $length
	 * @return string
	 */
	function random_string($length=255)
	{
		$chars = array();
		for ($i=0; $i<$length; $i++) {
			$chars[] = chr(rand(65, 122));
		}
		return implode("", $chars);
	}
}