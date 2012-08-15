<?php

require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));
class C_Test_Displayed_Gallery extends C_Test_Component_Base
{
	var $gallery_ids		= array();
	var $image_ids			= array();
	var $gal_mapper			= NULL;
	var $img_mapper 		= NULL;
	var $alb_mapper			= NULL;
	var $storage			= NULL;
	var $test_image_abspath	= NULL;


	function __construct()
	{
		parent::__construct();
		$this->gal_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
		$this->img_mapper = $this->get_registry()->get_utility('I_Gallery_Image_Mapper');
//		$this->alb_mapper = $this->get_registry()->get_utility('I_Album_Mapper');
		$this->storage	= $this->get_registry()->get_utility('I_Gallery_Storage');
		$this->test_image_abspath = path_join(__DIR__, 'test.jpg');
	}

	/***
	 * Creates some galleries, albums, tags, and images
	 */
	function setUp()
	{
		parent::setUp();

		// Get keys
		$gal_key	= $this->gal_mapper->get_primary_key_column();
		$img_key	= $this->img_mapper->get_primary_key_column();
//		$alb_key	= $this->alb_mapper->get_primary_key_column();

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


		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "A Test Image #2";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #3";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");

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

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "A Test Image #5";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");

		$image = $this->storage->upload_base64_image($gallery, $this->test_image_abspath);
		$image->alttext = "Test Image #6";
		$image->description = "This is a test image";
		if ($this->img_mapper->save($image)) {
			$this->image_ids[] = $image->$img_key;
		}
		else $this->fail("Could not create {$image->alttext}");
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
		$images = $displayed_gallery->get_images();
		$this->assertEqual(count($images), 3);
		$this->assertEqual($displayed_gallery->get_image_count(), 3);

		// Get the images for all galleries
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'gallery';
		$displayed_gallery->container_ids = $this->gallery_ids;
		$images = $displayed_gallery->get_images();
		$this->assertEqual(count($images), 6);
		$this->assertEqual($displayed_gallery->get_image_count(), 6);

		// Exclude one of the images
		$displayed_gallery->exclusions = array($this->image_ids[0]);
		$images = $displayed_gallery->get_images();
		$this->assertEqual(count($images), 5);
		$this->assertEqual($displayed_gallery->get_image_count(), 5);

		// Set limits
		$images = $displayed_gallery->get_images(2);
		$this->assertEqual($displayed_gallery->get_image_count(), 5);
		$this->assertEqual(count($images), 2);

		// Test ordering
		$displayed_gallery->container_ids = $this->gallery_ids[0];
		$displayed_gallery->order_by = 'alttext';
		$displayed_gallery->exclusions = array();
		$images = $displayed_gallery->get_images();
		$first_image = $images[0];
		$this->assertEqual(count($images), 3);
		$this->assertEqual($displayed_gallery->get_image_count(), 3);
		$this->assertEqual($first_image->alttext, "A Test Image #2");

		// Test getting specific image ids (first & last image)
		$displayed_gallery = $this->get_factory()->create('displayed_gallery');
		$displayed_gallery->source = 'gallery';
		$displayed_gallery->entity_ids = array(
			$this->image_ids[0],
			$this->image_ids[count($this->image_ids)-1]
		);
		$images = $displayed_gallery->get_images();
		$this->assertEqual(count($images), 2);
		$this->assertEqual($displayed_gallery->get_image_count(), 2);
		$this->assertEqual($images[0]->$image_key, $this->image_ids[0]);
		$this->assertEqual($images[1]->$image_key, $this->image_ids[count($this->image_ids)-1]);

		// Test getting specific image ids from a list of galleries, including
		// exclusions
		$displayed_gallery->container_ids = $this->gallery_ids;
		$displayed_gallery->order_by = 'sortorder';
		$images = $displayed_gallery->get_images();
		$this->assertEqual(count($images), 6);
		$this->assertEqual($displayed_gallery->get_image_count(), 6);
		$this->assertEqual($images[0]->$image_key, $this->image_ids[0]);
		$this->assertEqual($images[1]->$image_key, $this->image_ids[count($this->image_ids)-1]);
	}
}

?>
