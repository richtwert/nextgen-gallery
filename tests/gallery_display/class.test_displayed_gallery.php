<?php

require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

class C_Test_Displayed_Gallery extends C_Test_Component_Base
{
	var $gallery_ids		= array();
	var $image_ids			= array();
    var $album_ids          = array();
	var $gal_mapper			= NULL;
	var $img_mapper 		= NULL;
	var $alb_mapper			= NULL;
    var $gal_key            = NULL;
    var $img_key            = NULL;
    var $alb_key            = NULL;
	var $storage			= NULL;
	var $test_image_abspath	= NULL;


	function __construct()
	{
		parent::__construct();
		$this->gal_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
		$this->img_mapper = $this->get_registry()->get_utility('I_Image_Mapper');
		$this->alb_mapper = $this->get_registry()->get_utility('I_Album_Mapper');
		$this->storage	= $this->get_registry()->get_utility('I_Gallery_Storage');
		$this->test_image_abspath = path_join(dirname(__FILE__), 'test.jpg');
	}

	/***
	 * Creates some galleries, albums, tags, and images
	 */
	function setUp()
	{
		parent::setUp();

		// Get keys
        $this->gallery_ids              = array();
        $this->album_ids                = array();
		$this->gal_key  =   $gal_key	= $this->gal_mapper->get_primary_key_column();
		$this->img_key  =   $img_key	= $this->img_mapper->get_primary_key_column();
		$this->alb_key  =   $alb_key	= $this->alb_mapper->get_primary_key_column();

		// Create test gallery #1
		$gallery = new stdClass();
		$gallery->title = "Displayed Gallery Test Gallery #1";
		if ($this->gal_mapper->save($gallery)) {
			$this->gallery_ids[] = $gallery->$gal_key;
		}
		else $this->fail("Could not create {$gallery->title}");

		// Create three test images for this gallery
		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #1";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "A Test Image #2";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #3";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

		// Create test gallery #2
		$gallery = new stdClass();
		$gallery->title = "Displayed Gallery Test Gallery #2";
		if ($this->gal_mapper->save($gallery)) {
			$this->gallery_ids[] = $gallery->$gal_key;
		}
		else $this->fail("Could not create {$gallery->title}");

		// Create three test images for this gallery
		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #4";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "A Test Image #5";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #6";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
		sleep(1);

        // Create test album #1
        $album = new stdClass();
        $album->name = "Test Album #1";
        $album->sortorder = $this->gallery_ids;
        if ($this->alb_mapper->save($album)) {
            $this->album_ids[] = $album->$alb_key;
        }
        else $this->fail("Could not create {$album->name}");

        // Create test album #2
        $album = new stdClass();
        $album->name = "Test Album #2";
        $album->sortorder = array($this->gallery_ids[0]);
        if ($this->alb_mapper->save($album)) {
            $this->album_ids[] = $album->$alb_key;
        }
        else $this->fail("Could not create {$album->name}");

        // Create test album #3
        $album = new stdClass();
        $album->name = "Test Album #3";
        $album->sortorder = array($this->gallery_ids[count($this->gallery_ids)-1]);
        if ($this->alb_mapper->save($album)) {
            $this->album_ids[] = $album->$alb_key;
        }
        else $this->fail("Could not create {$album->name}");


        // Create test album #4
        $album = new stdClass();
        $album->name = "Test Album #4";
        $album->sortorder = array(
            'a'.$this->album_ids[0],
            $this->gallery_ids[0],
            'a'.$this->album_ids[1],
            $this->gallery_ids[1]
        );
        if ($this->alb_mapper->save($album)) {
            $this->album_ids[] = $album->$alb_key;
        }
        else $this->fail("Could not create {$album->name}");
	}

	/**
	 * Destroys galleries, albums, tags, etc created in setUp()
	 */
	function tearDown()
	{
		parent::tearDown();

		$img_key = $this->img_mapper->get_primary_key_column();

		foreach ($this->gallery_ids as $gallery_id) {
			$images = $this->img_mapper->find_all(array('galleryid = %s', $gallery_id));
			foreach ($images as $image) {
				$abspath = $this->storage->get_image_abspath($image);
				$this->storage->delete_image($image);
				$this->assertFalse(file_exists($abspath));
				$this->assertNull($this->img_mapper->find($image->$img_key));
			}
			$this->gal_mapper->destroy($gallery_id);
			$this->assertNull($this->gal_mapper->find($gallery_id));
		}

        foreach ($this->album_ids as $album_id) {
            $this->alb_mapper->destroy($album_id);
            $this->assertNull($this->alb_mapper->find($album_id));
        }
	}

	/**
	 * Tests getting images from a gallery source
	 */
	function test_get_gallery_images()
	{
		$image_key = $this->img_mapper->get_primary_key_column();

		// Get the images for the first gallery
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'gallery';
		$displayed_gallery->container_ids = $this->gallery_ids[0];
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 3);
		$this->assertEqual($displayed_gallery->get_entity_count(), 3);

		// Get the images for all galleries
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'gallery';
		$displayed_gallery->container_ids = $this->gallery_ids;
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 6);
		$this->assertEqual($displayed_gallery->get_entity_count(), 6);

		// Exclude one of the images
		$displayed_gallery->exclusions = array($this->image_ids[0]);
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 5);
		$this->assertEqual($displayed_gallery->get_entity_count(), 5);

		// Set limits
		$images = $displayed_gallery->get_entities(2);
		$this->assertEqual($displayed_gallery->get_entity_count(), 5);
		$this->assertEqual(count($images), 2);

		// Test ordering
		$displayed_gallery->container_ids = $this->gallery_ids[0];
		$displayed_gallery->order_by = 'alttext';
		$displayed_gallery->exclusions = array();
		$images = $displayed_gallery->get_entities();
		$first_image = $images[0];
		$this->assertEqual(count($images), 3);
		$this->assertEqual($displayed_gallery->get_entity_count(), 3);
		$this->assertEqual($first_image->alttext, "A Test Image #2");

		// Test getting specific image ids (first & last image)
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'gallery';
		$displayed_gallery->entity_ids = array(
			$this->image_ids[0],
			$this->image_ids[count($this->image_ids)-1]
		);
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 2);
		$this->assertEqual($displayed_gallery->get_entity_count(), 2);
		$this->assertEqual($images[0]->$image_key, $this->image_ids[0]);
		$this->assertEqual($images[1]->$image_key, $this->image_ids[count($this->image_ids)-1]);

		// Test getting specific image ids from a list of galleries, including
		// exclusions
		$displayed_gallery->container_ids = $this->gallery_ids;
		$displayed_gallery->order_by = 'sortorder';
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 6);
		$this->assertEqual($displayed_gallery->get_entity_count(), 6);
		$this->assertEqual($images[0]->$image_key, $this->image_ids[0]);
		$this->assertEqual($images[1]->$image_key, $this->image_ids[count($this->image_ids)-1]);
	}


	function test_get_recent_images()
	{
		// Test getting 5 of the most recent images
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'recent';
		$images = $displayed_gallery->get_entities(5);
		$this->assertEqual($displayed_gallery->get_entity_count(), count($this->img_mapper->find_all()));
		$this->assertEqual(count($images), 5);
		$this->assertEqual($images[0]->pid, $this->image_ids[count($this->image_ids)-1]);

		// Test the same, but exclude the most recent image
		$displayed_gallery->exclusions = array(
			$this->image_ids[count($this->image_ids)-1]
		);
		$images = $displayed_gallery->get_entities(5);
		$this->assertEqual($displayed_gallery->get_entity_count(), count($this->img_mapper->find_all())-1);
		$this->assertEqual(count($images), 5);
		$this->assertEqual($images[0]->pid, $this->image_ids[count($this->image_ids)-2]);
	}


	function test_get_random_images()
	{
		// Test retrieving 5 random images, from the test galleries we've created
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'random';
		$displayed_gallery->container_ids = $this->gallery_ids;
		$images = $displayed_gallery->get_entities();
		$this->assertEqual(count($images), 6);
		$this->assertEqual($displayed_gallery->get_entity_count(), 6);
		foreach ($images as $image) {
			$this->assertTrue(in_array($image->pid, $this->image_ids));
		}
	}


    function test_get_gallery_containers()
    {
        $displayed_gallery = $this->get_factory()->create('displayed_gallery');
        $displayed_gallery->source = 'gallery';
        $displayed_gallery->container_ids = $this->gallery_ids;
        $gal_key = $this->gal_key;
        $galleries = $displayed_gallery->get_gallery_containers();
        $this->assertEqual(count($this->gallery_ids), count($galleries));
        for ($i=0; $i<count($galleries); $i++) {
            $gallery = $galleries[$i];
            if (isset($this->gallery_ids[$i])) {
                $this->assertEqual($gallery->$gal_key, $this->gallery_ids[$i]);
            }
            else $this->fail("get_gallery_containers() returned an invalid gallery");
        }
    }


    function test_get_album_entities_by_container()
    {
        // Test fetching entities from a single album
        $displayed_gallery = $this->get_factory()->create('displayed_gallery');
        $displayed_gallery->source = 'album';
        $displayed_gallery->container_ids = array($this->album_ids[0]);
        $entities = $displayed_gallery->get_entities();
        $gal_key = $this->gal_key;
        $this->assertEqual(count($entities), count($this->gallery_ids));
        for ($i=0; $i<count($entities); $i++) {
            $gallery = $entities[$i];
            $this->assertEqual($gallery->$gal_key, $this->gallery_ids[$i]);
        }

        // Test fetching entities from multiple albums
        $displayed_gallery->container_ids = array_slice($this->album_ids, 1, 2);
        $entities = $displayed_gallery->get_entities();
        $this->assertEqual(count($entities), 2);

        // Test fetching an album which has galleries and sub-albums
        $displayed_gallery->container_ids = $this->album_ids[count($this->album_ids)-1];
        $entities = $displayed_gallery->get_entities();
        $this->assertEqual(count($entities), 4);
        $this->assert_is_album(array_shift($entities));
        $this->assert_is_gallery(array_shift($entities));
        $this->assert_is_album(array_shift($entities));
        $this->assert_is_gallery(array_shift($entities));

        // Test that limit works
        $entities = $displayed_gallery->get_entities(2, 1);
        $this->assertEqual(count($entities), 2);
        $this->assert_is_gallery(array_shift($entities));
        $this->assert_is_album(array_shift($entities));
    }


    function test_get_specific_album_entities()
    {
        // Test fetching specific entities to display as an album
        $displayed_gallery = $this->get_factory()->create('displayed_gallery');
        $displayed_gallery->source = 'album';
        $displayed_gallery->entity_ids = array(
          $this->gallery_ids[0],
          'a'.$this->album_ids[1],
          $this->gallery_ids[1]
        );
        $entities = $displayed_gallery->get_entities();
        $gal_key = $this->gal_key;
        $this->assertEqual(count($entities), 3);
        $this->assertEqual($entities[0]->$gal_key, $this->gallery_ids[0]);
        $this->assert_is_gallery(array_shift($entities));
        $this->assert_is_album(array_shift($entities));
        $this->assert_is_gallery(array_shift($entities));
//
//        // Test that limit works
        $entities = $displayed_gallery->get_entities(2, 1);
        $this->assertEqual(count($entities), 2);
        $this->assert_is_album(array_shift($entities));
        $this->assert_is_gallery(array_shift($entities));
    }


    function test_specific_albums_entities_with_containers()
    {
        $displayed_gallery = $this->get_factory()->create('displayed_gallery');
        $displayed_gallery->source = 'album';
        $displayed_gallery->entity_ids = array(
            'a'.$this->album_ids[1]
        );
        $displayed_gallery->container_ids = array(
            $this->album_ids[count($this->album_ids)-1]
        );
        $entities = $displayed_gallery->get_entities();
        $this->assertEqual(count($entities), 4);
        $this->assert_is_album($entities[0], 1);
        $this->assert_is_gallery($entities[1], 1);
        $this->assert_is_album($entities[2], 0);
        $this->assert_is_gallery($entities[3], 1);
    }


    /**
     * Asserts that an entity is an album
     * @param $entity
     */
    function assert_is_album($entity, $excluded=NULL)
    {
        $alb_key = $this->alb_key;
        $album = $this->alb_mapper->find($entity->$alb_key);
        $this->assertEqual($entity->name, $album->name);
        if (!is_null($excluded)) $this->assertTrue( $entity->exclude == $excluded);
    }


    /**
     * Asserts an entity is a gallery
     * @param $entity
     */
    function assert_is_gallery($entity, $excluded=NULL)
    {
        $gal_key = $this->gal_key;
        if (isset($entity->$gal_key)) {
            $gallery = $this->gal_mapper->find($entity->$gal_key);
            $this->assertEqual($entity->name, $gallery->name);
            if (!is_null($excluded)) $this->assertTrue( $entity->exclude == $excluded);
        }
        else $this->fail("Entity is not a gallery");
    }
}
