<?php
require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

/**
 * Tests the Photocrati Options class. This class provides a utility for
 * retrieving options related to the plugin
 */
class C_Test_Photocrati_Options extends C_Test_Component_Base
{
	/**
	 * Backs up the options in the database, and then flushes all options
	 */
	function setUp()
	{
		// Backup original options
		$this->backup = get_option('I_Photocrati_Options');
		delete_option('C_Photocrati_Options');
	}


	/**
	 * Restores the backup of options
	 */
	function tearDown()
	{
		update_option('C_Photocrati_Options', $this->backup);
	}


	/**
	 * Tests instantiating the options class
	 */
	function test_create()
	{
		// The options class is a registered utility
		$this->options = $this->get_registry()->get_utility('I_Photocrati_Options');
		$this->assertTrue(get_class($options), 'C_Photocrati_Options');

		// The options class can also be instantiated, but this isn't recommended
		$this->options = new C_Photocrati_Options();
	}


	/**
	 * Test default values for the options class
	 */
	function test_default_values()
	{
		// You access options as if they were object properties
		$this->assertEqual($this->options->graphicLibrary, 'gd');
		$this->assertEqual($this->options->gallery_storage_driver, 'wordpress_gallery_storage');
		$this->assertEqual($this->init_version, FALSE);
	}


	/**
	 * Tests changing option values and ensuring they persist
	 */
	function test_changing_values()
	{
		// You change an option by setting it's property and calling the save()
		// method
		$this->options->thumbquality = 90;
		$this->options->save();

		// Re-fetch the options class to ensure we've got the values from
		// the database
		$this->options = $this->get_registry()->get_utility('I_Photocrati_Options');
		$this->assertTrue($this->options->thumbquality, 90);
	}


	/**
	 * Tests the behavior of the class when requesting a non-existing value
	 */
	function test_non_existing_values()
	{
		$this->assertNull($this->options->foobar);
	}
}