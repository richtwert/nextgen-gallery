<?php

require_once(path_join(NEXTGEN_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

class C_Test_Nextgen_Settings extends C_Test_Component_Base
{
	/**
	 * The NextGen Settings and multisite settings instances
     *
	 * @var C_NextGen_Settings
	 */
	public $settings;
    public $multi_settings;

	function __construct($label='C_NextGen_Settings Test')
	{
		parent::__construct($label);
	}

	/**
	 * Test the NextGen Settings setup
	 */
	function setUp()
	{
		delete_site_option('ngg_options');
		delete_option('ngg_options');

		// We can get the instance from the component registry as a utility
		$this->settings = $this->get_registry()->get_singleton_utility('I_NextGen_Settings', array());
		$this->assertEqual(get_class($this->settings), 'C_NextGen_Settings');

        $this->multi_settings = $this->get_registry()->get_singleton_utility('I_NextGen_Settings', array('multisite'));
        $this->assertEqual(get_class($this->multi_settings), 'C_NextGen_Settings');

        $this->settings->reset();
        $this->multi_settings->reset();
	}

    /**
     * Test retrieving options
     *
     * Because we have array, class, and getter access we do repeat ourselves a bit here
     */
    function test_get()
    {
        $C_NextGen_Settings_Defaults = new C_NextGen_Settings_Defaults();
        $defaults = $C_NextGen_Settings_Defaults->get_defaults();
        $multi_defaults = $C_NextGen_Settings_Defaults->get_defaults(True);

        // test for __get access, ArrayAccess access, and ->get() and compare the result with the defaults
        $this->assertTrue(
            isset($this->settings->imgHeight),
            'Could not access settings->imgHeight'
        );

        $this->assertTrue(
            isset($this->settings['imgHeight']),
            'Could not access settings[imgHeight]'
        );

        $this->assertEqual(
            $this->settings->get('imgHeight'),
            $defaults['imgHeight'],
            'get(imgHeight) did not equal $defaults[imgHeight]'
        );

        $this->assertEqual(
            $this->multi_settings->get('wpmuCSSfile'),
            $multi_defaults['wpmuCSSfile'],
            'get(wpmuCSSfile) did not equal $multi_defaults[wpmuCSSfile]'
        );
    }

    /**
     * Test is_set() and isset()
     */
    function test_is_set()
    {
        $this->assertTrue(
            $this->settings->is_set('imgHeight'),
            'is_set(imgHeight) returned False'
        );

        $this->assertTrue(
            isset($this->settings->imgHeight),
            'isset(settings->imgHeight) returned False'
        );

        $this->assertTrue(
            isset($this->settings['imgHeight']),
            'isset(settings[imgHeight]) returned False'
        );

        $this->assertFalse(
            $this->settings->is_set('foo_bar'),
            'is_set(foo_bar) returned True'
        );

        $this->assertFalse(
            isset($this->settings->foo_bar),
            'isset(settings->foo_bar) returned True'
        );

        $this->assertFalse(
            isset($this->settings['foo_bar']),
            'isset(settings[foo_bar] returned True'
        );
    }

	/**
	 * Test saving a new option
	 */
	function test_set_del()
	{
        $str = 'foo bar';
        $this->settings->set('foo_bar', $str);
        $this->assertEqual(
            $this->settings->get('foo_bar'),
            $str,
            'settings->set(foo_bar, str) = ... did not work'
        );

        $str = 'foo_bar';
        $this->settings->foo_bar = $str;
        $this->assertEqual(
            $this->settings->get('foo_bar'),
            $str,
            'settings->foo_bar = ... did not work'
        );

        $str = 'bar_foo';
        $this->settings['foo_bar'] = $str;
        $this->assertEqual(
            $this->settings->get('foo_bar'),
            $str,
            'settings[foo_bar] = ... did not work'
        );

        /*
         * now test del()
         */
        $this->settings->del('foo_bar');
        $this->assertFalse(
            $this->settings->is_set('foo_bar'),
            'del(foo_bar) did not run correctly'
        );

        $this->settings->foo_bar ='test';
        unset($this->settings->foo_bar);
        $this->assertFalse(
            $this->settings->is_set('foo_bar'),
            'del(foo_bar) did not run correctly'
        );

        $this->settings['foo_bar'] = 'test';
        unset($this->settings['foo_bar']);
        $this->assertFalse(
            $this->settings->is_set('foo_bar'),
            'del(foo_bar) did not run correctly'
        );
    }

    /**
     * Test saving (database persistence) of options to Wordpress
     */
    function test_wordpress_save()
    {
        $this->settings->set('foo_bar', 'test');
		$this->settings->save();
		$options = get_option('ngg_options', array());
		$this->assertTrue(
            array_key_exists('foo_bar', $options),
            'foo_bar was not saved to the db'
        );

        // reset(True) causes a sync of the defaults to the db
        $this->settings->reset(True);
        $options = get_option('ngg_options', array());
        $this->assertFalse(
            array_key_exists('foo_bar', $options),
            'foo_bar still in database after reset(True) was called'
        );
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
	 * Tests the reload() function
	 */
	function test_reload()
	{
		$this->settings->test_setting = True;
		$this->assertTrue(isset($this->settings->test_setting));
		$this->settings->reload();
		$this->assertFalse(
            isset($this->settings->test_setting),
            'settings->reload() did not remove settings->test_setting'
        );
	}

    /**
     * Tests the to_array() function
     */
    function test_to_array()
    {
        $this->assertTrue(
            is_array($this->settings->to_array()),
            'settings->to_array() did not return an array'
        );
        $this->assertTrue(
            is_array($this->multi_settings->to_array()),
            'multi_settings->to_array() did not return an array'
        );
    }

    /**
     * Tests multisite operations
     */
    function test_multisite()
    {
        // Assure that gallerypath default is set properly
        $this->assertTrue(
            isset($this->multi_settings->gallerypath),
            'multi_settings->gallerypath does not exist'
        );
        $this->assertEqual(
            $this->multi_settings->get('gallerypath'),
            'wp-content/blogs.dir/%BLOG_ID%/files/',
            'gallerypath did not hold the expected value'
        );

        // Test that the gallerypath gets overwritten by the global option in an MU environment
        $GLOBALS['NGG_MULTISITE'] = True;
        $this->multi_settings->reset();

        $this->assertEqual(
            $this->multi_settings->gallerypath,
            'wp-content/blogs.dir/'. get_current_blog_id() . '/files/',
            '_apply_multisite_overrides() did not modify gallerypath correctly'
        );

        // Test that the gallerypath does NOT get overwritten by the global option in a non-MU environment
        $GLOBALS['NGG_MULTISITE'] = False;
        $this->settings->reset();
        $this->assertEqual(
            $this->settings->gallerypath,
            'wp-content/gallery/',
            'settings->gallerypath did not hold the expected value'
        );
    }

    function test_restore_defaults()
    {
        $C_NextGen_Settings_Defaults = new C_NextGen_Settings_Defaults();
        $defaults = $C_NextGen_Settings_Defaults->get_defaults();

        $this->assertEqual(
            $this->settings->get('imgHeight'),
            $defaults['imgHeight'],
            'get(imgHeight) did not equal $defaults[imgHeight]'
        );

        $GLOBALS['NGG_MULTISITE'] = True;
        $this->multi_settings->reset();
        unset($this->settings->imgHeight);
        unset($this->multi_settings->wpmuCSSfile);
        $this->settings->restore_all_missing_options();
        $GLOBALS['NGG_MULTISITE'] = False;

        $this->assertEqual(
            $this->settings->get('imgHeight'),
            $defaults['imgHeight'],
            'get(imgHeight) did not equal $defaults[imgHeight]'
        );

        $this->assertEqual(
            $this->multi_settings->get('gallerypath'),
            'wp-content/blogs.dir/'. get_current_blog_id() . '/files/',
            'restore_all_missing_options() did not work or _apply_multisite_overrides() was not called'
        );
    }

    function tearDown()
    {
        delete_site_option('ngg_options');
        delete_option('ngg_options');
    }
}
