<?php

include_once('class.test_gallerystorage_driver_base.php');
abstract class C_Test_NggLegacy_GalleryStorage_Driver_Base extends C_Test_GalleryStorage_Driver_Base
{
	var $test_file_abspath = '';
	var $new_datamapper_driver = '';
	var $new_gallerystorage_driver = '';
	var $original_datamapper_driver = '';
	var $original_gallerystorage_driver = '';

	function __construct($label, $datamapper_driver_factory_method)
	{
		parent::__construct($label);
		$this->settings = $this->get_registry()->get_utility('I_NextGen_Settings');
		$this->new_datamapper_driver			= $datamapper_driver_factory_method;
		$this->new_gallerystorage_driver		= 'ngglegacy_gallery_storage';
		$this->original_datamapper_driver		= $this->settings->datamapper_driver;
		$this->original_gallerystorage_driver	= $this->settings->gallerystorage_driver;
		$this->test_file_abspath = path_join(dirname(__FILE__), 'test.jpg');
	}

	/**
	 * Create a gallery and image for testing purposes
	 */
	function setUp()
	{
		parent::setUp();

		// Change the datamapper and gallery storage drivers
		$this->settings->gallerystorage_driver  = $this->new_gallerystorage_driver;
		$this->settings->datamapper_driver		= $this->new_datamapper_driver;
		$this->settings->save();

		// Get the mappers required for these tests
		$this->gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
		$this->image_mapper   = $this->get_registry()->get_utility('I_Gallery_Image_Mapper');

		// Create test gallery to work with
		$this->gallery = (object) array(
			'title'	=>	'NextGen Gallery'
		);
		$this->gid = $this->gallery_mapper->save($this->gallery);
		$this->assertTrue(is_int($this->gid) && $this->gid > 0, "Could not create new gallery");

		// Create image to work with
		$this->image = (object) array(
			'alttext'		=>	'test-image',
			'filename'	=>	'test.jpg',
			'galleryid'	=>	$this->gid
		);
		$this->pid = $this->image_mapper->save($this->image);
		$this->assert_valid_image($this->image, $this->image_mapper->get_primary_key_column());

		$this->galleries_to_cleanup = array();
		$this->images_to_cleanup = array();
	}


	function tearDown()
	{
		parent::tearDown();

		// Restore the datamapper and gallery storage drivers
		$this->settings->gallerystorage_driver  = $this->original_gallerystorage_driver;
		$this->settings->datamapper_driver		= $this->original_datamapper_driver;
		$this->settings->save();

		// Delete any temporary galleries and images we might have created
		$this->gallery_mapper->destroy($this->gid);
		$this->image_mapper->destroy($this->pid);

		foreach ($this->galleries_to_cleanup as $gid) {
			$this->gallery_mapper->destroy($gid);
		}

		foreach ($this->images_to_cleanup as $pid) {
			$this->image_mapper->destroy($pid);
		}
	}


	/**
	 * Demonstate how to instantiate the gallery storage driver
	 */
	function test_gallerystorage_constructor()
	{
		$this->storage = $this->get_factory()->create('ngglegacy_gallery_storage');
		$this->assertEqual(get_class($this->storage), 'C_NggLegacy_GalleryStorage_Driver');
		$this->assert_valid_gallerystorage_driver();
	}


	/**
	 * Tests uploading an image. When an image has been uploaded, thumbnails
	 * get created automatically
	 */
	function test_upload_image()
	{
		$image_key = $this->image_mapper->get_primary_key_column();

		// We upload files using the upload_image() method of the
		// C_Gallery_Storage class. The first parameter is the
		// gallery_id or an object representing the gallery
		foreach (array($this->gid, $this->gallery) as $gallery) {

			// Run upload test if our previous gallery creation tests passed
			if ($gallery) {

				// You can upload an image from $_FILES
				$_FILES['file'] = array(
					'name'		=>	'test.jpg',
					'type'		=>	'type/jpeg',
					'tmp_name'	=>	$this->test_file_abspath,
					'error'		=>	0
				);
				$image = $this->storage->upload_image($gallery);
				$this->assert_valid_image($image, $image_key);
				$this->images_to_cleanup[] = $image->$image_key;

				// Or you can upload an image using base64 data
				$img = $this->storage->upload_image($gallery, 'test-base64.jpg', file_get_contents($this->test_file_abspath));
				$this->images_to_cleanup[] = $image->$image_key;
				$this->assert_valid_image($image, $image_key);

				foreach(array($img, $img->$image_key) as $image) {

					// Get the full-sized image dimensions
					$dimensions = $this->storage->get_image_dimensions($image);
					$this->assert_valid_dimensions($dimensions);

					// get_full_dimensions() is an alias to get_image_dimensions()
					$this->assertEqual(
						$dimensions,
						$this->storage->get_full_dimensions($image)
					);

					// get_original_dimensions() is an alias to get_image_dimensions()
					$this->assertEqual(
						$dimensions,
						$this->storage->get_original_dimensions($image)
					);

					// Get the thumbnail-sized image dimensions
					$dimensions = $this->storage->get_thumbnail_dimensions($image);
					$this->assert_valid_dimensions($dimensions);

					// get_thumb_dimensions is an alias to get_thumbnail_dimensions()
					$this->assertEqual(
						$dimensions,
						$this->storage->get_thumbnail_dimensions($image)
					);
				}
			}
		}
	}

	/**
	 * You can see what image sizes are registered for all gallery images
	 * Both driver implementations support a minimum of 'thumbnail' and 'full'.
	 */
	function test_get_images_sizes()
	{
		$sizes = $this->storage->get_image_sizes();
		$this->assertTrue(is_array($sizes));
		$this->assertTrue(in_array('thumbnail', $sizes));
		$this->assertTrue(in_array('full', $sizes));
	}


	function test_get_upload_abspath()
	{

		// The get_upload_abs_path() method accepts the gallery id or an object
		// representing the gallery to be passed as the first argument
		$gallery = $this->gallery_mapper->find($this->gid);
		$this->assertTrue(is_object($gallery));

		// We'll need the settings utility to get the configured gallerypath
		$settings = $this->get_registry()->get_utility('I_NextGen_Settings');
		$rel_upload_dir = $settings->gallerypath;

		// Set some path expectations
		$abs_upload_dir = path_join(ABSPATH, $rel_upload_dir);
		$abs_gallery_dir = path_join($abs_upload_dir, $gallery->slug);

		// Test the get_upload_abspath() method
		$this->assertEqual($this->storage->get_upload_abspath(), $abs_upload_dir);
		$this->assertEqual($this->storage->get_upload_abspath($gallery), $abs_gallery_dir);

		// Let's get the path stored for the gallery in the database. In
		// this case, it will be the same as the upload directory
		// for the gallery
		$this->assertEqual($this->storage->get_gallery_abspath($gallery), $abs_gallery_dir);
	}


	/**
	 * Tests getting the absolute path and filename for a gallery image
	 */
	function test_get_image_abspath()
	{
		foreach (array($this->image, $this->pid) as $image) {
			// Set some path assumptions
			$abs_image_path = $this->storage->get_image_abspath($image);
			$abs_gallery_path = $this->storage->get_gallery_abspath($this->gid);

			// Test get_image_abspath()
			$copy_of_image = $this->image_mapper->find($image);
			$this->assertEqual(
				$abs_image_path,
				path_join($abs_gallery_path, $copy_of_image->filename)
			);

			// Test get_full_abspath(), which is an alias to get_image_abspath()
			$this->assertEqual(
				$this->storage->get_full_abspath($image),
				$abs_image_path
			);

			// Test get_original_abspath(), another alias to get_image_abspath()
			$this->assertEqual(
				$this->storage->get_original_abspath($image),
				$abs_image_path
			);

			// Test the get_image_abspath for thumbnails
			$this->assertTrue(strpos(
				$this->storage->get_image_abspath($image, 'thumbs'),
				path_join($abs_gallery_path, 'thumbs')
			) === 0);
		}
	}


	/**
	 * Tests getting urls for the image
	 */
	function test_get_image_urls()
	{
		foreach (array($this->image, $this->pid) as $image) {

			// Get the url to the full-sized image
			$url = $this->storage->get_image_url($image);
			$this->assertTrue(strpos($url, $this->image->filename) !== FALSE);

			// get_original_url() is an alias to get_image_url()
			$this->assertTrue($url, $this->storage->get_original_url($image));

			// get_full_url() is an alias to get_image_url()
			$this->assertTrue($url, $this->storage->get_full_url($image));

			// Get the url to the thumbnail-sized image
			$thumb_url = $this->storage->get_image_url($image, 'thumbs');
			$this->assertTrue(strpos($thumb_url, $this->image->filename) !== FALSE);
			$this->assertTrue(strpos($thumb_url, 'thumbs') !== FALSE);

			// get_thumbs_url() is an alias to get_thumbnail_url()
			$this->assertTrue(
				$this->storage->get_image_url($image, 'thumbs'),
				$this->storage->get_thumbnail_url($image)
			);
		}
	}


	/**
	 * Tests getting the HTML tag for an image
	 */
	function test_get_image_html()
	{
		foreach (array($this->image, $this->pid) as $image) {

			// Get the html for the full-sized image
			$html = $this->storage->get_image_html($image);
			$this->assert_valid_img_tag($html, $image, $this->storage->get_full_url($image));

			// get_full_html() is an alias for get_image_html()
			$this->assertEqual($html, $this->storage->get_full_html($image));

			// get_original_html() is an alias for get_image_html()
			$this->assertEqual($html, $this->storage->get_original_html($image));
		}
	}


	/**
	 * Tests getting the HTML tag for a thumbnail image
	 */
	function test_get_thumbnail_html()
	{
		foreach (array($this->image, $this->pid) as $image) {

			// Get the html for the full-sized image
			$html = $this->storage->get_thumbnail_html($image);
			$this->assert_valid_img_tag($html, $image, $this->storage->get_thumbnail_url($image));

			// get_thumb_html() is an alias for get_thumbnail_html()
			$this->assertEqual($html, $this->storage->get_thumb_html($image));
			$this->assert_valid_img_tag($html, $image, $this->storage->get_thumbnail_url($image));
		}
	}


	function test_generate_thumbnail()
	{
		foreach (array($this->image, $this->pid) as $image) {
			// Recreate the thumbnail for the uploaded image
			$this->assertTrue($this->storage->generate_thumbnail($image));
			$this->assertTrue(file_exists($this->storage->get_thumb_abspath($image)));
			$this->assert_valid_dimensions($this->storage->get_thumbnail_dimensions($image));
		}
	}


	function test_deleting_all_images_from_filesystem()
	{
		$this->storage->delete_image($this->image);
		$this->assertFalse(file_exists($this->storage->get_image_abspath($this->image)));
		$this->assertFalse(file_exists($this->storage->get_thumb_abspath($this->image)));
	}


	function test_deleting_thumbnail_from_filesystem()
	{
		$img = $this->storage->upload_image(
			$this->gallery,
			'test-base64.jpg',
			file_get_contents($this->test_file_abspath))
		;
		$this->storage->delete_image($img, 'thumbnail');
		$this->assertFalse(file_exists($this->storage->get_thumb_abspath($img)));
		$this->assertTrue(file_exists($this->storage->get_full_abspath($img)));
	}


//	/**
//	 * Tests getting the backup path
//	 */
//	function test_backups()
//	{
//		foreach (array($this->image, $this->pid) as $image) {
//
//			$path = $this->storage->get_image_backup_abspath($image);
//			$this->assertEqual(
//				path_join($this->storage->get_image_abspath($image), '_backup'),
//				$path
//			);
//
//			$this->assertTrue($this->storage->backup_image($image));
//			$this->assertTrue(file_exists($path));
//		}
//	}
//
//
//	function test_move_images()
//	{
//		// Test move operation
//		$gallery = (object) array(
//			'title'	=>	'Another Gallery'
//		);
//		$this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
//		$image = $this->image_mapper->find($this->images_to_cleanup[0]);
//		$images = array($image);
//		$this->assertTrue($this->storage->move_images($images, $gallery));
//
//		// Ensure that new image path has been set
//		$gallery_path = $this->storage->get_gallery_abspath($gallery);
//		$this->assertEqual(
//			path_join($gallery_path, $image->filename),
//			$this->storage->get_image_abspath($image)
//		);
//
//		// Ensure that new image thumbnail path has been set
//		$gallery_thumb_path = $this->storage->get_gallery_thumbnail_abspath($gallery);
//		$this->assertEqual(
//			path_join($gallery_thumb_path, $image->filename),
//			$this->storage->get_thumbnail_abspath($image)
//		);
//	}
//
//
//	function test_copy_images()
//	{
//		// Test copy operation
//		$gallery = (object) array(
//			'title'	=>	'Another Gallery'
//		);
//		$this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
//		$image = $this->image_mapper->find($this->images_to_cleanup[1]);
//		$images = array($image);
//		$new_image_ids = $this->storage->move_images($images, $gallery);
//		$this->assertTrue(is_array($new_image_ids));
//
//		// Ensure that the new new images have the correct paths
//		$gallery_path = $this->storage->get_gallery_abspath($gallery);
//		$gallery_thumb_path = $this->storage->get_gallery_thumbnail_abspath($gallery);
//		foreach ($new_image_ids as $image_id) {
//			$this->assertEqual(
//				path_join($gallery_thumb_path, $image->filename),
//				$this->storage->get_thumbnail_abspath($image)
//			);
//
//			$this->assertEqual(
//				path_join($gallery_path, $image->filename),
//				$this->storage->get_image_abspath($image)
//			);
//		}
//	}
//
//
//	/*** HELPER METHODS ******************************************************/
//
	/**
	 * Asserts that an image is a valid image
	 * @param type $image
	 */
	function assert_valid_image($image, $image_key)
	{
		// Make assertions
		if (get_class($image) == 'C_Test_NggLegacy_GalleryStorage_Driver') {
		}
		$this->assertTrue(
			in_array(get_class($image), array('stdClass','C_NextGen_Gallery_Image')),
			"Image is not a stdClass or C_NextGen_Gallery_Image instance"
		);
		$this->assertTrue(is_int($image->$image_key), "Image ID is not an integer");
		$this->assertTrue($image->$image_key > 0, "Image ID is not greater than zero");
		if ($image instanceof stdClass) {
			$image = $this->image_mapper->convert_to_model($image);
		}
		$this->assertTrue($image->is_valid(), "Image is not valid");
		$this->assertFalse($image->is_invalid(), "Image is invalid");
		$this->assertNotEmpty($image->galleryid, "Image has no gallery id");
		$this->assertNotEmpty($image->filename, "Image has no filename");
	}

	/**
	 * Asserts that an array of dimensions are valid
	 * @param array $dimensions
	 */
	function assert_valid_dimensions($dimensions)
	{
		$this->assertTrue(is_array($dimensions), "Dimensions is not an array");
		$this->assertTrue(isset($dimensions['width']), "Dimensions array did not include a width");
		$this->assertTrue(isset($dimensions['height']), "Dimensions array did not include a height");
		$this->assertTrue($dimensions['height'] > 0, "Image width was not an integer greater than 0");
		$this->assertTrue($dimensions['width'] > 0, "Image height was not an integer greater than 0");
	}

	/**
	 * Asserts that the string consists of a valid image tag
	 * @param string $html
	 * @param string $url
	 */
	function assert_valid_img_tag($html, $image, $url)
	{
		if (is_int($image)) $image = $this->image_mapper->find($image);
		$this->assertTrue(is_object($image), "Image is not an object");
		$url		= preg_quote($url, '/');
		$alttext	= preg_quote($image->alttext, '/');
		$this->assertPattern("/src=['\"]{$url}['\"]/", $html, "Image tag does not contain the correct 'src' attribute: %s");
		$this->assertPattern("/alt=['\"]{$alttext}['\"]/", $html, "Image tag does not contain the correct 'alt' attribute: %s");
		$this->assertPattern("/title=['\"]{$alttext}['\"]/", $html, "Image tag does not contain the correct 'title' attribute: %s");
	}
}

?>