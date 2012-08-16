<?php
require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

/**
 * Tests the interface for DataMapper Drivers
 */
abstract class C_Test_DataMapper_Driver_Base extends C_Test_Component_Base
{
	public $post_title = "Mike's Test Post";
	public $post_type = 'post';
	public $model_factory_method = 'my_model';
	public $post_id = 0;
	public $mapper;
	var $original_datamapper_driver = "";
	var $settings = NULL;
	var $new_datamapper_driver = '';


	/**
	 * Constructs a new test case for a datamapper driver
	 * @param string $title
	 */
	function __construct($title, $datamapper_driver)
	{
		parent::__construct($title);
		$this->post_title = "Mike's Test Post";
		$this->post_type = 'post';
		$this->model_factory_method = 'my_model';
		$this->ids_to_cleanup = array();
		$this->new_datamapper_driver = $datamapper_driver;
		$this->settings = $this->get_registry()->get_utility('I_NextGen_Settings');
		$this->original_datamapper_driver = $this->settings->datamapper_driver;
	}


	function setUp()
	{
		parent::setUp();
		$this->settings->datamapper_driver = $this->new_datamapper_driver;
		$this->settings->save();
	}


	function tearDown()
	{
		parent::tearDown();
		$this->settings->datamapper_driver = $this->original_datamapper_driver;
		$this->settings->save();
	}


	/**
	 * Tests creating new entities using the data mapper
	 */
	function test_crud_operations()
	{
		// You can create a new entity by using the save() method
		// of the data mapper.
		//
		// You can pass a stdClass object to the save method
		// The save method will always return the ID of the new entity
		// or FALSE if there was a problem
		$entity = (object) array('post_title' => $this->post_title);
		$result = $this->mapper->save($entity);
		$this->assert_valid_entity($entity, $result, TRUE);


		// The save method is also used to save an existing entity
		$number_of_posts = $this->mapper->count();
		$entity->post_name = "foobar";
		$this->mapper->save($entity);
		$this->assertEqual($entity->post_name, "foobar");
		$this->assertEqual($number_of_posts, $this->mapper->count());

		// The destroy method is used to delete an entity. The destroy()
		// method can be passed the ID of the record, or an entity object
		// The following example passes in an entire entity object, but
		// the ID is tested in @see tearDown()
		$this->assertTrue($this->mapper->destroy($entity));

		// You can also pass a model (subclass of C_DataMapper_Model) to the
		// save method
		$entity = new C_DataMapper_Model($this->mapper, array('post_title' => $this->post_title));
		$this->post_id = $result = $this->mapper->save($entity);
		$this->assert_valid_entity($entity, $result, TRUE);

		// A model allows you to incorporate business logic for entities, such
		// as validation. We'll add a mixin that adds validation to ensure
		// that all posts have a post_title field set
		$entity->add_mixin('Mock_Mixin_DataMapper_Model_Validations');
		$entity->post_title = NULL;
		$this->assertFalse($this->mapper->save($entity));
		$this->assertTrue(count($entity->errors_for('post_title')==2));
		$this->assertTrue($entity->is_invalid());
		$this->assertFalse($entity->is_valid());

		// One of the type of entities we'd like to support in the future
		// is associative arrays/hashes. The problem with using them is that
		// we have to use references, as the entity passed to save() gets
		// modified to include other data (such as IDs).
		//
		// For now, if an associative array is attempted, then we throw an
		// exception
		try {
			$this->mapper->save(array('post_title' => $this->post_title));
		}
		catch (E_InvalidEntityException $ex) {
			$this->pass("Caught E_InvalidEntityException when trying to use array");
		}

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
		// By default, datamappers are not associated with any model class
		$this->assertEqual(FALSE, $this->mapper->get_model_factory_method());

		// Set a fake factory method
		$this->mapper->set_model_factory_method($this->model_factory_method);
		$this->assertEqual(
			$this->model_factory_method,
			$this->mapper->get_model_factory_method()
		);
	}

	/**
	 * Search for posts using find methods
	 */
	function test_find_methods()
	{
		// To find an existing post, just use the find() method and pass the
		// ID of the record you wish to retrieve
		$entity = $this->mapper->find($this->post_id);
		$this->assertIsA($entity, 'stdClass');
		$this->assert_valid_entity($entity);

		// You may also optionally ask for a model class to be returned
		// See @test_model_factory_methods()
		$entity = $this->mapper->find($this->post_id, TRUE);
		$this->assertIsA($entity, 'C_DataMapper_Model');
		$this->assert_valid_entity($entity);

		// When attempting to find a record that doesn't exist, NULL will be
		// returned
		$entity = $this->mapper->find(PHP_INT_MAX);
		$this->assertNull($entity);

		// You can also try finding a post based on other conditions
		$entity = $this->mapper->find_first(
			array('post_title IN (%s)', $this->post_title)
		);
		$this->assert_valid_entity($entity);

		// Like the find() method, find_first() will return NULL if no record
		// is found
		$entity = $this->mapper->find_first(
			array('post_title = %s', $this->random_string())
		);
		$this->assertNull($entity);

		// Like the find() method, find_first() can also return a model
		$entity = $this->mapper->find_first(
			array('post_title IN (%s)', $this->post_title),TRUE
		);
		$this->assertIsA($entity, 'C_DataMapper_Model');
		$this->assertEqual($entity->post_title, $this->post_title);

		// Create a new entity
		$another_post_name = 'foo-123';
		$this->ids_to_cleanup[]= $retval = $this->mapper->save((object)array(
			'post_name'		=> $another_post_name,
		));
		$this->assertTrue($retval > 0);

		// Use the find_all() method to find all known entities
		$results = $this->mapper->find_all();
		$key = $this->mapper->get_primary_key_column();
		$this->assertTrue(count($results) >= 2);

		// Test to ensure that the results contain the two posts that we know
		// about
		$found_ids = array();
		foreach ($results as $entity) $found_ids[] = $entity->$key;
		$matches = array_intersect(
			$found_ids,
			array_merge($this->ids_to_cleanup, array($this->post_id))
		);
		$this->assertTrue(count($matches)>=2);

		// The find_all() method can also accept a where clause
		$results = $this->mapper->find_all(array('post_name = %s', $another_post_name));
		$this->assertEqual(count($results), 1);
		if ($results) {
			$this->assertEqual($results[0]->post_name, $another_post_name);
		}

		// The find_all() method can also return models
		$results = $this->mapper->find_all(array('post_name = %s', $another_post_name), TRUE);
		$this->assertEqual(count($results), 1);
		if ($results) {
			$this->assertEqual($results[0]->post_name, $another_post_name);
			$this->assertIsA($results[0], 'C_DataMapper_Model');
		}
	}


	/**
	 * Search for posts using custom queries
	 */
	function test_custom_queries()
	{
		$key = $this->mapper->get_primary_key_column();

		// Create some test entries
		$this->ids_to_cleanup[]= $retval = $this->mapper->save((object)array(
			'post_name'		=> 'test_123',
			'post_password'	=> 'foobar',
			'post_title'	=> 'A Title'
		));
		$this->assertTrue($retval > 0);
		$this->ids_to_cleanup[] = $retval = $this->mapper->save((object)array(
			'post_name'		=> 'test_321',
			'post_password'	=> 'foobar',
			'post_title'	=> 'Za Title'
		));
		$this->assertTrue($retval > 0);

		// Find the above entities using custom queries
		$results = $this->mapper->select($key)->where(
			array('post_password = %s', 'foobar')
		)->run_query();
		$this->assertTrue(count($results) == 2);
		foreach ($results as $entity) {
			$this->assertTrue(in_array($entity->$key, $this->ids_to_cleanup));
		}

		// Find the above entries using multiple values in the where clause,
		// and order the results
		$results = $this->mapper->select('post_name, post_title')->where(
			array('post_name IN (%s, %s)', 'test_123', 'test_321')
		)->order_by('post_title', 'DESC')->run_query();
		$this->assertTrue(count($results) == 2);
		foreach ($results as $entity) {
			$this->assertTrue(in_array($entity->post_name, array('test_123', 'test_321')));
		}
		if ($results) $this->assertEqual($results[0]->post_title, 'Za Title');

		// You can also limit the results
		$results = $this->mapper->select('post_name, post_title')->where(
			array('post_name IN (%s, %s)', 'test_123', 'test_321')
		)->order_by('post_title', 'DESC')->limit(1)->run_query();
		$this->assertEqual(count($results), 1);
		if ($results) $this->assertEqual($results[0]->post_title, 'Za Title');

		// You can also specify multiple where conditions
		$results = $this->mapper->select()->where(array(
			array('post_password = %s', 'foobar'),
			array('post_title = %s', 'A Title')
		))->run_query();
		$this->assertEqual(count($results), 1);
		if ($results) {
			$this->assertEqual($results[0]->post_title, 'A Title');
		}

		// The where() method is an alias for where_and()
		$results = $this->mapper->select()->where_and(array(
			array('post_password = %s', 'foobar'),
			array('post_title = %s', 'A Title')
		))->run_query();
		$this->assertEqual(count($results), 1);
		if ($results) {
			$this->assertEqual($results[0]->post_title, 'A Title');
		}

		// There is also a where_or() method
		// TODO: Is there any way we can add where_or() support to
		// C_CustomPost_DataMapper_Driver ?
		/**
		$results = $this->mapper->select()->where_or(array(
			array('post_title = %s', 'A Title'),
			array('post_title = $s', 'Za Title')
		))->run_query();
		$this->assertEqual(count($results), 2);
		foreach ($results as $entity) {
			$this->assertTrue(in_array(
				$entity->post_title, array('A Title', 'Za Title')
			));
		}
		**/
	}


	/**
	 * After the tests, delete any newly created posts
	 */
	function test_delete()
	{
		foreach (array_merge($this->ids_to_cleanup, array($this->post_id)) as $id) {
			if (($index = array_search($id, $this->ids_to_cleanup)) !== FALSE) {
				unset($this->ids_to_cleanup[$index]);

				// The destroy method can be used to delete entities by ID
				$this->assertTrue($this->mapper->destroy($id));
				$this->assertNull($this->mapper->find($id));
			}
		}
	}


	/*************************************************************************
	**
	 * HELPER METHODS
	**
	**************************************************************************/

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


	/**
	 * Returns the expected primary key. Should be overloaded
	 * by subclass
	 * @return string
	 */
	function get_expected_primary_key()
	{
		return '';
	}


	/**
	 * Asserts that an entity using the datamapper is valid
	 * @param stdClass $entity
	 */
	function assert_valid_entity($entity, $retval=FALSE, $test_retval=FALSE)
	{
		$key = $this->get_expected_primary_key();

		if ($test_retval) {
			$this->assertTrue((is_int($retval) && $retval>0), "save() did not return a valid record ID: ".gettype($retval));
			$this->assertEqual($retval, $entity->$key);
		}
		$this->assertEqual($entity->id_field, $key, "id_field was not set to the primary key");
		$this->assertTrue((is_int($entity->$key) && $entity->$key>0), "save() did not return a valid record ID: ".gettype($retval));
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
		$this->assertEqual($mapper->get_primary_key_column(), $this->get_expected_primary_key());
		$this->assertEqual($mapper->get_table_name(), $table_prefix.'posts');
		$this->assertEqual($mapper->get_object_name(), $this->post_type);
	}
}

?>
