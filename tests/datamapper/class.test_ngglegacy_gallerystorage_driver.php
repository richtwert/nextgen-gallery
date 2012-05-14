<?php

include_once('class.gallerystorage_driver_base.php');
class C_Test_NggLegacy_GalleryStorage_Driver extends C_Test_GalleryStorage_Driver_Base
{

	/**
	 * Create a gallery and image for testing purposes
	 */
	function setUp()
	{
		parent::setUp();

		$this->gallery_mapper = $this->_get_registry()->get_utility('I_Gallery_Mapper');
		$this->image_mapper   = $this->_get_registry()->get_utility('I_Gallery_Image_Mapper');
		$this->gallery = (object) array(
			'title'	=>	'Test Gallery'
		);
		$this->gid = $gallery_mapper->save($gallery);
		$this->galleries_to_cleanup = array();
		$this->images_to_cleanup = array();
	}


	function tearDown()
	{
		parent::tearDown();
		$this->gallery_mapper->destroy($this->gid);

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

		// We upload files using the upload_image() method of the
		// C_Gallery_Storage class. The first parameter is the
		// gallery_id or an object representing the gallery
		foreach (array($this->gid, $this->gallery) as $gallery) {

			// You can upload an image from $_FILES, a file stored locally
			$test_img_filename = path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'test.png');
			$image = $this->storage->upload_image($gallery, $test_img_filename);
			$this->assertTrue(is_object($image));
			$image_key = $this->image_mapper->get_primary_key_column();
			$this->assertTrue(is_int($image->$image_key));
			$this->assertTrue($image->$image_key > 0);
			$this->image_mapper->destroy($image);

			// Or you can upload an image from $_FILE
			$this->image = $this->storage->upload_image($gallery, file_get_contents($test_img_filename), TRUE);
			$image->pid = $this->image->$image_key;
			$this->images_to_cleanup[] = $this->image->$image_key;
			$this->assertTrue(is_object($image));
			$this->assertTrue(is_int($image->$image_key));
			$this->assertTrue($image->$image_key > 0);

			// Not sure how to test this but, you can upload an image using
			// plupload and $_FILES
			// $this->storage->upload_image($this->gid);
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
		foreach (array($this->gid, $this->gallery) as $gallery) {

			// Get the root upload path. Shouldn't ever be required by API
			$options = $this->_get_registry()->get_singleton_utility('I_Photocrati_Options');
			$upload_path = $this->storage->get_upload_abspath();
			$this->assertEqual($options->gallerypath, $upload_path);

			// Get the upload path for a particular gallery. Otherwise, known as
			// the gallery path
			$gallery_path = $this->storage->get_upload_abspath($gallery);
			$this->assertEqual(path_join($options->gallerypath, $gallery), $gallerypath);

			// The above method is aliases to get_gallery_abspath()
			$gallery_path = $this->storage->get_gallery_abspath($gallery);
			$this->assertEqual($this->storage->get_upload_abspath($gallery), $gallerypath);
		}
	}


	/**
	 * Tests getting the absolute path and filename for a gallery image
	 */
	function test_get_image_abspath()
	{
		// The get_image_abspath() and related methods accept an image id or
		// object representing the image as a parameter
		foreach (array($this->pid, $this->image) as $image) {

			// Get the absolute path of the image
			$image_path = $this->storage->get_image_abspath($image);
			$this->assertEqual(
				path_join($this->storage->get_gallery_abspath($this->gid), $this->image->filename),
				$image_path
			);

			// get_full_abspath() is an alias to get_image_abspath()
			$this->assertEqual(
				$this->get_image_abspath($image),
				$this->get_full_abspath($image)
			);

			// get_original_abspath() is an alias to get_image_abspath()
			$this->assertEqual(
				$this->get_image_abspath($image),
				$this->get_original_abspath($image)
			);
		}
	}

	/**
	 * Tests getting the absolute path where thumbnails are stored for
	 * a particular gallery
	 */
	function test_get_gallery_thumbnail_abspath()
	{

		foreach (array($this->gallery, $this->gid) as $gallery) {
			$gallery_path = $this->storage($gallery);
			$this->assertEqual(
				path_join($gallery_path, 'thumbs'),
				$this->storage->get_gallery_thumbnail_abspath($gallery)
			);
		}
	}


	/**
	 * Tests getting the absolute path of the thumbnail image
	 */
	function test_get_image_thumbnail_abspath()
	{
		foreach (array($this->image, $this->pid) as $image) {

			$gallery_path = $this->storage($this->gid);
			$thumbnail_path = $this->storage->get_thumbnail_abspath($image);
			$this->assertTrue(
				strpos($thumbnail_path, path_join($gallery_path, 'thumbs')) !== FALSE
			);

			// get_thumb_abspath() is an alias to get_thumbnail_abspath()
			$this->assertTrue(
				$thumbnail_path,
				$this->storage->get_thumbs_abspath($image)
			);
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
			$thumb_url = $this->storage->get_thumbnail_url($image);
			$this->assertTrue(strpos($url, $this->image->filename) !== FALSE);
			$this->assertTrue(strpos($url, 'thumbs') !== FALSE);

			// get_thumbs_url() is an alias to get_thumbnail_url()
			$this->assertTrue(
				$this->storage->get_thumbs_url($image),
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
			$this->assert_valid_html($html);

			// get_full_html() is an alias for get_image_html()
			$this->assertEqual($html, $this->storage->get_full_html($image));

			// get_original_html() is an alias for get_image_html()
			$this->assertEqual($html, $this->storage->get_originall_html($image));
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
			$this->assert_valid_html($html, $this->get_thumbnail_url($image));

			// get_thumb_html() is an alias for get_thumbnail_html()
			$this->assertEqual($html, $this->storage->get_thumb_html($image));
			$this->assert_valid_html($html, $this->get_thumbnail_url($image));
		}
	}


	/**
	 * Tests getting image dimensions
	 */
	function test_get_image_dimensions()
	{
		foreach(array($this->image, $this->pid) as $image) {

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
				$this->storage->get_thumbanil_dimensions($image)
			);
		}
	}


	function test_create_thumbnail()
	{
		foreach (array($this->image, $this->pid) as $image) {
			// Recreate the thumbnail for the uploaded image
			$this->storage->create_thumbnail($image);
			$this->assertTrue(file_exists($this->storage->get_thumb_abspath($image)));
		}
	}


	/**
	 * Tests getting the backup path
	 */
	function test_backups()
	{
		foreach (array($this->image, $this->pid) as $image) {

			$path = $this->storage->get_image_backup_abspath($image);
			$this->assertEqual(
				path_join($this->storage->get_image_abspath($image), '_backup'),
				$path
			);

			$this->assertTrue($this->storage->backup_image($image));
			$this->assertTrue(file_exists($path));
		}
	}


	function test_move_images()
	{
		// Test move operation
		$gallery = (object) array(
			'title'	=>	'Another Gallery'
		);
		$this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
		$image = $this->image_mapper->find($this->images_to_cleanup[0]);
		$images = array($image);
		$this->assertTrue($this->storage->move_images($images, $gallery));

		// Ensure that new image path has been set
		$gallery_path = $this->storage->get_gallery_abspath($gallery);
		$this->assertEqual(
			path_join($gallery_path, $image->filename),
			$this->storage->get_image_abspath($image)
		);

		// Ensure that new image thumbnail path has been set
		$gallery_thumb_path = $this->storage->get_gallery_thumbnail_abspath($gallery);
		$this->assertEqual(
			path_join($gallery_thumb_path, $image->filename),
			$this->storage->get_thumbnail_abspath($image)
		);
	}


	function test_copy_images()
	{
		// Test copy operation
		$gallery = (object) array(
			'title'	=>	'Another Gallery'
		);
		$this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
		$image = $this->image_mapper->find($this->images_to_cleanup[1]);
		$images = array($image);
		$new_image_ids = $this->storage->move_images($images, $gallery);
		$this->assertTrue(is_array($new_image_ids));

		// Ensure that the new new images have the correct paths
		$gallery_path = $this->storage->get_gallery_abspath($gallery);
		$gallery_thumb_path = $this->storage->get_gallery_thumbnail_abspath($gallery);
		foreach ($new_image_ids as $image_id) {
			$this->assertEqual(
				path_join($gallery_thumb_path, $image->filename),
				$this->storage->get_thumbnail_abspath($image)
			);

			$this->assertEqual(
				path_join($gallery_path, $image->filename),
				$this->storage->get_image_abspath($image)
			);
		}
	}


	/*** HELPER METHODS ******************************************************/

	function assert_valid_dimensions($dimensions)
	{
		$this->assertTrue(is_array($dimensions));
		$this->assertTrue(isset($dimensions['width']));
		$this->assertTrue(isset($dimensions['height']));
		$this->assertTrue($dimensions['height'] > 0);
		$this->assertTrue($dimensions['width'] > 0);
	}


	function assert_valid_html($html, $url)
	{
		$url = preg_quote($url, '/');
		$alt = preg_quote($this->image->alttext);
		$title = preg_quote($this->image->title);
		$this->assertTrue(preg_match("/src=['\"]{$url}['\"]/", $html));
		$this->assertTrue(preg_match("/alt=['\"]{$alt}['\"]/", $html));
		$this->assertTrue(preg_match("/title=['\"]{$title}['\"]/", $html));
	}
}

?>
