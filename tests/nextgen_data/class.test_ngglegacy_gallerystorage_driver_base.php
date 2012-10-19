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
		$this->image_mapper   = $this->get_registry()->get_utility('I_Image_Mapper');

		// Create test gallery to work with
		$this->gallery = (object) array(
			'title'	=>	'NextGen Gallery'
		);
		$this->gid = $this->gallery_mapper->save($this->gallery);
		$this->assertTrue(is_int($this->gid) && $this->gid > 0, "Could not create new gallery");

		// Create image to work with
		$this->image = (object) array(
			'alttext'		=>	'test-image',
			'filename'	=>	'test-base64.jpg',
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


	/**
	 * Tests getting the backup path
	 */
	function test_recover_image()
	{
        $orig_image_path = $this->storage->get_image_abspath($this->image);
        $new_image_path  = $orig_image_path . '_backup';

        @copy($orig_image_path, $new_image_path);

        $this->assertTrue(file_exists($orig_image_path));
        $this->assertTrue(file_exists($new_image_path));

        unlink($orig_image_path);

        $this->assertFalse(file_exists($orig_image_path));
        $this->assertTrue(file_exists($new_image_path));

        $retcode = $this->storage->recover_image($this->image);
        $this->assertEqual(1, $retcode, 'recover_images() did not return success');

        $this->assertTrue(file_exists($orig_image_path));
        $this->assertTrue(file_exists($new_image_path));

        unlink($new_image_path);
	}

    /**
     * Tests copy_images() with db=FALSE
     *
     * move_images() is a wrapper to copy_images(). The tests for both operations build up together.
     *
     * Disabled until gallery storage unit tests are fixed
     */
    function test_copy_images()
    {
        return;

        $gallery = (object) array('title' => 'Test Copy Images Gallery');
        $this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
        $gallery = $this->gallery_mapper->find($gallery);
        $orig_image = $this->image_mapper->find($this->image);

        // when db=FALSE copy_images() returns an array of the images successfully copied
        $new_image_ids = $this->storage->copy_images(array($this->image), $gallery, FALSE);

        // find our original image again so we can make sure it's db entry wasn't altered
        $new_image = $this->image_mapper->find(reset($new_image_ids));

        $this->assertTrue(
            is_array($new_image_ids),
            'copy_images() return value was not an array'
        );
        $this->assertEqual(
            1,
            count($new_image_ids),
            'copy_images() returned multiple values for a single image'
        );
        $this->assertTrue(
            is_file($this->storage->get_image_abspath($orig_image)),
            'Original file was removed during copy operation'
        );
        $this->assertTrue(
            is_file($gallery->path . DIRECTORY_SEPARATOR . $orig_image->filename),
            'Test file was not copied'
        );
        $this->assertEqual(
            $orig_image,
            $new_image,
            'DB entry for original image was altered'
        );

        // because no db entry was created for automatic purging
        if (is_file($gallery->path . DIRECTORY_SEPARATOR . $orig_image->filename))
        {
            unlink($gallery->path . DIRECTORY_SEPARATOR . $orig_image->filename);
        }
    }

    /**
     * Tests copy_images() with db=TRUE
     *
     * Disabled until gallery storage unit tests are fixed
     */
    function test_copy_images_db()
    {
        return;

        $gallery = (object) array('title' => 'Test Copy Images DB Gallery');
        $this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);

        $gallery          = $this->gallery_mapper->find($gallery);
        $gallery_id_field = $gallery->id_field;

        $orig_image    = $this->image_mapper->find($this->image);
        $new_image_ids = $this->storage->copy_images(array($this->image), $gallery, TRUE);
        $new_image     = $this->image_mapper->find(reset($new_image_ids));
        $this->images_to_cleanup[] = $new_image;

        $this->assertEqual(
            $new_image->galleryid,
            $gallery->$gallery_id_field
        );

        $this->assertEqual(
            array('pid', 'galleryid'),
            array_keys(
                array_diff(
                    (array)$orig_image,
                    (array)$new_image
                )
            ),
            'DB entry for new image has not changed or has changed too much'
        );
    }

    /**
     * Tests move_images() with db=FALSE
     *
     * Disabled until gallery storage unit tests are fixed
     */
    function test_move_images()
    {
        return;

        $gallery = (object) array('title' => 'Test Move Images Gallery');
        $this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);
        $gallery = $this->gallery_mapper->find($gallery);

        $orig_image    = $this->image_mapper->find($this->image);
        $new_image_ids = $this->storage->move_images(array($this->image), $gallery, FALSE);
        $new_image     = $this->image_mapper->find(reset($new_image_ids));
        $this->images_to_cleanup[] = $new_image;

        $this->assertFalse(
            is_file($this->storage->get_image_abspath($orig_image)),
            'Original file was not moved'
        );

        $this->assertTrue(
            is_file($gallery->path . DIRECTORY_SEPARATOR . $orig_image->filename),
            'move_images() destination file does not exist'
        );
    }

    /**
     * Tests move_images() with db=TRUE
     *
     * Disabled until gallery storage unit tests are fixed
     */
    function test_move_images_db()
    {
        return;

        $gallery = (object) array('title' => 'Test Move Images DB Gallery');
        $this->galleries_to_cleanup[] = $this->gallery_mapper->save($gallery);

        $gallery          = $this->gallery_mapper->find($gallery);
        $gallery_id_field = $gallery->id_field;

        $orig_image    = $this->image_mapper->find($this->image);
        $new_image_ids = $this->storage->move_images(array($this->image), $gallery, TRUE);
        $new_image     = $this->image_mapper->find(reset($new_image_ids));
        $this->images_to_cleanup[] = $new_image;

        $this->assertEqual(
            $new_image->galleryid,
            $gallery->$gallery_id_field
        );

        $this->assertEqual(
            array('pid', 'galleryid'),
            array_keys(
                array_diff(
                    (array)$orig_image,
                    (array)$new_image
                )
            ),
            'DB entry for new image has not changed or has changed too much'
        );
    }

    /**
     * generate_image_clone() handles the file operations (resizing, cropping, watermarks, etc)
     */
    function test_generate_image_clone()
    {
        $orig_image_path = $this->storage->get_image_abspath($this->image);
        $dest_image_path = $orig_image_path . '_test_generate_image_clone';

        // these exist outside the db, so we must clean them up manually
        $image_files = array();

        // when given empty parameters it should return NULL
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, array());
        @$image_files[] = $image->fileName;
        $this->assertNull($image, 'Returned non null result with empty parameters array');

        // our file should come back named like test-50x33.jpg
        $params = array('width' => 50);
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;
        $this->assertEqual(
            basename($image->fileName),
            pathinfo(
                $orig_image_path, PATHINFO_FILENAME)
                . "-{$image->currentDimensions['width']}x{$image->currentDimensions['height']}."
                . pathinfo($orig_image_path, PATHINFO_EXTENSION
            ),
            'Returned image has an incorrect filename'
        );

        $this->assertTrue(
            is_file($image->fileName),
            'Cloned image file does not exist'
        );

        $this->assertFalse(
            $image->error,
            sprintf('Reported error: %s', $image->errmsg)
        );

        $this->assertTrue(
            (md5_file($orig_image_path) != md5_file($image->fileName)),
            'File md5sum unchanged'
        );

        $this->_generate_image_clone_effects($orig_image_path,    $dest_image_path, $image_files);
        $this->_generate_image_clone_dimensions($orig_image_path, $dest_image_path, $image_files);

        foreach ($image_files as $file) {
            if (is_file($file))
            {
                unlink($file);
            }
        }
    }

    function _generate_image_clone_dimensions($orig_image_path, $dest_image_path, &$image_files)
    {
        // test image width first
        $params = array('width' => 50);
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;
        $size = getimagesize($image->fileName);

        $this->assertEqual(
            $image->currentDimensions['width'],
            $params['width'],
            'Returned width not the same as requested'
        );

        $this->assertEqual(
            $image->currentDimensions['width'],
            $size[0],
            'Incorrect width metadata returned'
        );

        // test height
        $params = array('height' => 50);
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;
        $size = getimagesize($image->fileName);

        $this->assertEqual(
            $image->currentDimensions['height'],
            $params['height'],
            'Returned height was not the same as requested'
        );

        $this->assertEqual(
            $image->currentDimensions['height'],
            $size[1],
            'Incorrect height metadata returned'
        );
    }

    function _generate_image_clone_effects($orig_image_path, $dest_image_path, &$image_files)
    {
        $size = getimagesize($orig_image_path);
        $orig_image_md5 = md5_file($orig_image_path);

        // test compression quality
        $params = array(
            'width' => $size[0],
            'quality' => 1
        );
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;

        $this->assertTrue(
            is_file($image->fileName),
            'Was not able to clone image with a quality setting'
        );

        $this->assertNotEqual(
            md5_file($image->fileName),
            $orig_image_md5,
            'Image quality setting was not applied'
        );

        // test reflections
        $params = array(
            'reflection' => TRUE,
            'width' => $size[0]
        );
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;

        $this->assertTrue(
            is_file($image->fileName),
            'Was not able to clone image with a reflection'
        );

        $this->assertNotEqual(
            md5_file($image->fileName),
            $orig_image_md5,
            'Image reflection was not applied'
        );

        // test watermarks
        $params = array(
            'watermark' => TRUE,
            'width' => $size[0]
        );
        $image = $this->storage->generate_image_clone($orig_image_path, $dest_image_path, $params);
        $image_files[] = $image->fileName;

        $this->assertEqual(
            is_file($image->fileName),
            'Was not able to clone image with a watermark'
        );

        $this->assertNotEqual(
            md5_file($image->fileName),
            $orig_image_md5,
            'Image watermark was not applied'
        );
    }

    /*
     * generate_image_size() is a wrapper to generate_image_clone() that also handles the metadata
     * and database work of copying images
     */
    function test_generate_image_size_thumbnail()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');
        $orig_image_path = $this->storage->get_image_abspath($this->image);

        $image = $this->storage->generate_image_size($this->image, 'thumbnail');

        $this->assertTrue(
            is_file($image->fileName),
            'Cloned image file does not exist'
        );

        $this->assertFalse(
            $image->error,
            sprintf('Reported error: %s', $image->errmsg)
        );

        $this->assertTrue(
            (md5_file($orig_image_path) != md5_file($image->fileName)),
            'File md5sum unchanged'
        );

        $this->assertEqual(
            $image->currentDimensions,
            array(
                'width'  => $settings->thumbwidth,
                'height' => $settings->thumbheight
            ),
            'Thumbnail generation did not use default thumbnail dimensions'
        );

        $old_image = $this->image_mapper->find($this->image);
        $this->assertEqual(
            basename($image->fileName),
            $old_image->meta_data['thumbnail']['filename'],
            'Thumbnail filename was not saved to the parent image attributes'
        );
    }

    function test_generate_image_size_full_no_auto_resize()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');
        $orig_imgAutoResize = $settings->imgAutoResize;
        $settings->imgAutoResize = FALSE;
        $image = $this->storage->generate_image_size($this->image, 'full');

        $this->assertFalse(
            $image,
            'Returned image should be false when imgAutoResize == False'
        );

        $settings->imgAutoResize = $orig_imgAutoResize;
    }

    function test_generate_image_size_full_with_auto_resize()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');
        $orig_imgAutoResize = $settings->imgAutoResize;
        $settings->imgAutoResize = TRUE;

        $image = $this->storage->generate_image_size($this->image, 'full');

        $this->assertTrue(
            is_file($image->fileName),
            'Thumbnail file does not exist'
        );

        $this->assertEqual(
            $image->currentDimensions,
            array(
                'width'  => $settings->imgWidth,
                'height' => $settings->imgHeight
            )
        );

        $size = getimagesize($image->fileName);
        $this->assertEqual(
            $image->currentDimensions,
            array(
                'width'  => $size[0],
                'height' => $size[1]
            )
        );

        $settings->imgAutoResize = $orig_imgAutoResize;
    }

    function test_generate_image_size_thumbnail_with_dimension_parameters()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');

        $params = array(
            'width' => 50
        );
        $image = $this->storage->generate_image_size($this->image, 'thumbnail', $params);

        $this->assertTrue(
            is_file($image->fileName),
            'Thumbnail file does not exist'
        );

        // passing a lone width/height to generate_image_clone() will give an image with the right aspect ratio
        // but generate_image_size fills in default values. Expect a width of 50 and a height of 600 (or your
        // default) here.
        $this->assertEqual(
            $image->currentDimensions,
            array(
                'width'  => $params['width'],
                'height' => $settings->thumbheight
            )
        );

        $size = getimagesize($image->fileName);
        $this->assertEqual(
            $image->currentDimensions,
            array(
                'width'  => $size[0],
                'height' => $size[1]
            )
        );
    }

    function test_generate_image_size_watermark()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');
        $orig_wm_text = $settings->wmText;
        $settings->wmText = 'Generate Image Size';

        $image = $this->storage->generate_image_size($this->image, 'thumbnail');

        $this->assertEqual(
            $image->watermarkText,
            $settings->wmText,
            'Thumbnail generation did not use default watermark text'
        );

        $settings->wmText = $orig_wm_text;
    }

    function test_generate_image_size_skip_parameters()
    {
        $settings = $this->get_registry()->get_utility('I_NextGen_Settings');
        $orig_wm_text = $settings->wmText;
        $settings->wmText = 'Generate Image Size';

        $image = $this->storage->generate_image_size($this->image, 'thumbnail', NULL, TRUE);

        $this->assertEqual(
            $image->watermarkText,
            '',
            'Thumbnail generation applied a watermark it should not have'
        );

        $settings->wmText = $orig_wm_text;
    }

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
			in_array(get_class($image), array('stdClass','C_Image')),
			"Image is not a stdClass or C_Image instance"
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
