<?php

require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));
class C_Test_Nextgen_Settings extends C_Test_Component_Base
{
	/**
	 * The NextGen Settings instance
	 * @var C_NextGen_Settings
	 */
	var $settings;

	/**
	 * Test the different ways of getting an instance of the NextGEN Settings
	 * object
	 */
	function setUp()
	{
		$this->ngg_mu_options = get_site_option('ngg_options');
		$this->ngg_options = get_option('ngg_options');
		delete_site_option('ngg_options');
		delete_option('ngg_options');

		// We can get the instance directly
		$this->settings = C_NextGen_Settings::get_instance();
		$this->assertEqual(get_class($this->settings), 'C_NextGen_Settings');

		// We can get the instance from the component registry as a utility
		$this->settings = $this->get_registry()->get_singleton_utility('I_NextGen_Settings');
		$this->assertEqual(get_class($this->settings), 'C_NextGen_Settings');
	}

	/**
	 * Tests that default settings being applied
	 */
	function test_defaults()
	{
		// We should be more comprehensive in our assertions here, but we're
		// only going to test for a few defaults. Any defaults added in future
		// version should be accompanied with an assertion.
		//
		// Assure that gallerypath default is set properly
		$this->assertTrue(isset($this->settings->gallerypath));
		$this->assertEqual(
			$this->settings->get_global('gallerypath'),
			'wp-content/blogs.dir/%BLOG_ID%/files/'
		);

		// Test that the gallerypath gets overwritten by the global option
		// in an MU environment
		$GLOBALS['NGG_MULTISITE'] = TRUE;
		$this->settings->reset();
		$this->assertEqual(
			$this->settings->gallerypath,
			'wp-content/blogs.dir/'.get_current_blog_id().'/files/'
		);

		// Test that the gallerypath does NOT get overwritten by the global
		// option in a non-MU environment
		$GLOBALS['NGG_MULTISITE'] = FALSE;
		$this->settings->reset();
		$this->assertEqual(
			$this->settings->gallerypath,
			'wp-content/gallery/'
		);
	}

	/**
	 * Test saving a new option as a setting
	 */
	function test_new_option()
	{
		$this->assertFalse(isset($this->settings->foo_bar));
		$this->settings->foo_bar = "Foo Bar";
		$this->assertTrue($this->settings->foo_bar, "Foo Bar");
		$this->assertNull($this->settings->get_global('foo_bar'));
		$this->settings->save();

		$options = get_option('ngg_options', array());
		$this->assertTrue(in_array('foo_bar', $options));
	}

	/**
	 * Tests the is_global_option() function
	 */
	function test_is_global_option()
	{
		$this->assertTrue($this->settings->is_global_option('wpmuCSSfile'));
		$this->assertFalse($this->settings->is_global_option('activateCSS'));
	}


	/**
	 * Tests the reload method
	 */
	function test_reload()
	{
		$this->settings->test_setting = TRUE;
		$this->assertTrue(isset($this->settings->test_setting));
		$this->settings->reload();
		$this->assertFalse(isset($this->settings->test_setting));
	}


	function tearDown()
	{
		$this->settings->reset(TRUE);
		update_option('ngg_options', $this->ngg_options);
		update_site_option('ngg_options', $this->ngg_mu_options);
	}
}

?>
