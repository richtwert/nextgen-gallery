<?php

class E_UploadException extends RuntimeException
{
	function __construct($message='', $code=NULL, $previous=NULL)
	{
		if (!$message) $message = "There was a problem uploading the file.";
		parent::__construct($message, $code, $previous);
	}
}

class E_InsufficientWriteAccessException extends RuntimeException
{
	function __construct($message='', $code=NULL, $previous=NULL)
	{
		if (!$message) $message = "Could not write to file. Please check filesystem permissions.";
		parent::__construct($message, $code, $previous);
	}
}

class E_NoSpaceAvailableException extends RuntimeException
{
	function __construct($message='', $code=NULL, $previous=NULL)
	{
		if (!$message) $message = "You have exceeded your storage capacity. Please remove some files and try again.";
		parent::__construct($message, $code, $previous);
	}
}

class Mixin_GalleryStorage_Driver_Base extends Mixin
{
	/**
	 * Set correct file permissions (taken from wp core). Should be called
	 * after writing any file
	 *
	 * @class nggAdmin
	 * @param string $filename
	 * @return bool $result
	 */
	function _chmod($filename = '')
	{
		$stat = @ stat( dirname($filename) );
		$perms = $stat['mode'] & 0000666; // Remove execute bits for files
		if ( @chmod($filename, $perms) )
			return TRUE;

		return FALSE;
	}


		/**
	 * Gets the absolute path where the full-sized image is stored
	 * @param int|object $image
	 */
	function get_full_abspath($image)
	{
		return $this->object->get_image_abspath($image, 'full');
	}


	/**
	 * Gets the upload path, optionally for a particular gallery
	 * @param int|C_NextGen_Gallery|stdClass $gallery
	 */
	function get_upload_relpath($gallery=FALSE)
	{
		return str_replace(ABSPATH, '', $this->object->get_upload_abspath($gallery));
	}


	/**
	 * An alias for get_full_abspath()
	 * @param int|object $image
	 */
	function get_original_abspath($image)
	{
		return $this->object->get_image_abspath($image, 'full');
	}


	/**
	 * Gets the dimensions for a particular-sized image
	 * @param int|object $image
	 * @param string $size
	 * @return array
	 */
	function get_image_dimensions($image, $size='full')
	{
		$retval = NULL;

		// If an image id was provided, get the entity
		if (is_numeric($image)) $image = $this->object->_image_mapper->find($image);

		// Ensure we have a valid image
		if ($image) {

			// Adjust size parameter
			switch ($size) {
				case 'original':
					$size = 'full';
					break;
				case 'thumbnails':
				case 'thumbnail':
				case 'thumb':
				case 'thumbs':
					$size = 'thumbnail';
					break;
			}

			// Image dimensions are stored in the $image->meta_data
			// property for all implementations
			if (isset($image->meta_data) && isset($image->meta_data[$size])) {
				$retval = $image->meta_data[$size];
			}
		}

		return $retval;
	}


	/**
	 * Alias to get_image_dimensions()
	 * @param int|object $image
	 * @return array
	 */
	function get_full_dimensions($image)
	{
		return $this->object->get_image_dimensions($image, 'full');
	}


	/**
	 * Alias to get_image_dimensions()
	 * @param int|object $image
	 * @return array
	 */
	function get_original_dimensions($image)
	{
		return $this->object->get_image_dimensions($image, 'full');
	}




	/**
	 * Gets the absolute path of the backup of an original image
	 * @param string $image
	 */
	function get_backup_abspath($image)
	{
		$retval = NULL;

		if (($image_path = $this->object->get_image_abspath($image))) {
			$retval = $image_path.'_backup';
		}

		return $retval;
	}


	/**
	 * Gets the HTML for an image
	 * @param int|object $image
	 * @param string $size
	 * @return string
	 */
	function get_image_html($image, $size='full')
	{
		$retval = "";

		if (is_numeric($image)) $image = $this->object->_image_mapper->find($image);

		if ($image) {

			// Get the image properties
			$alttext = esc_attr($image->alttext);
			$title	 = $alttext;

			// Get the dimensions
			$dimensions = $this->object->get_image_dimensions($image, $size);

			// Get the image url
			$image_url = $this->object->get_image_url($image, $size);

			$retval = implode(' ', array(
				'<img',
				"alt=\"{$alttext}\"",
				"title=\"{$title}\"",
				"src=\"{$image_url}\"",
				"width=\"{$dimensions['width']}\"",
				"height=\"{$dimensions['height']}\"",
				'/>'
			));
		}

		return $retval;
	}


	/**
	 * Alias to get_image_html()
	 * @param int|object $image
	 * @return string
	 */
	function get_original_html($image)
	{
		return $this->object->get_image_html($image, 'full');
	}


	/**
	 * Alias to get_image_html()
	 * @param int|object $image
	 * @return string
	 */
	function get_full_html($image)
	{
		return $this->object->get_image_html($image, 'full');
	}


	/**
	 * Backs up an image file
	 * @param int|object $image
	 */
	function backup_image($image)
	{
		$retval = FALSE;

		if (($image_path = $this->object->get_image_abspath($image))) {
			$retval = copy($image_path, $this->object->get_backup_abspath($image));
		}

		return $retval;
	}


	/**
	 * Copies images into another gallery
	 * @param array $images
	 * @param int|object $gallery
	 * @param boolean $db optionally only copy the image files
	 * @param boolean $move move the image instead of copying
	 */
	function copy_images($images, $gallery, $db=TRUE, $move=FALSE)
	{
		$retval = FALSE;

		// Ensure we have a valid gallery
		if (($gallery = $this->object->_get_gallery_id($gallery))) {
			$gallery_path = $this->object->get_gallery_path($gallery);
			$image_key = $this->object->_image_mapper->get_primary_key_column();
			$retval = TRUE;

			// Iterate through each image to copy...
			foreach ($images as $image) {

				// Copy each image size
				foreach ($this->object->get_image_sizes() as $size) {
					$image_path = $this->object->get_image_abspath($image, $size);
					$dst = path_join($gallery_path, basename($image_path));
					$success = $move ? move($image_path, $dst) : copy($image_path, $dst);
					if (!$success) $retval = FALSE;
				}

				// Copy the db entry
				if ($db) {
					if (is_numeric($image)) $this->object->_image_mapper($image);
					unset($image->$image_key);
					$image->galleryid = $gallery;
				}
			}
		}

		return $retval;
	}


	/**
	 * Moves images from to another gallery
	 * @param array $images
	 * @param int|object $gallery
	 * @param boolean $db optionally only move the image files, not the db entries
	 * @return boolean
	 */
	function move_images($images, $gallery, $db=TRUE)
	{
		return $this->object->copy_images($images, $gallery, $db, TRUE);
	}


	/**
	 * Gets the url to the original-sized image
	 * @param int|stdClass|C_NextGen_Gallery_Image $image
	 * @return string
	 */
	function get_original_url($image)
	{
		return $this->object->get_image_url($image, 'full');
	}


	/**
	 * Alias for get_original_url()
	 * @param int|stdClass|C_NextGen_Gallery_Image $image
	 * @return string
	 */
	function get_full_url($image)
	{
		return $this->object->get_image_url($image, 'full');
	}


	/**
	 * Gets the id of a gallery, regardless of whether an integer
	 * or object was passed as an argument
	 * @param mixed $gallery_obj_or_id
	 */
	function _get_gallery_id($gallery_obj_or_id)
	{
		$retval = NULL;

		$gallery_key = $this->object->_gallery_mapper->get_primary_key_column();
		if (is_object($gallery_obj_or_id)) {
			if (isset($gallery_obj_or_id->$gallery_key)) {
				$retval = $gallery_obj_or_id->$gallery_key;
			}
		}
		elseif(is_numeric($gallery_obj_or_id)) {
			$retval = $gallery_obj_or_id;
		}

		return $retval;
	}


	/**
	 * Gets the id of an image, regardless of whether an integer
	 * or object was passed as an argument
	 * @param type $image_obj_or_id
	 */
	function _get_image_id($image_obj_or_id)
	{
		$retval = NULL;

		$image_key = $this->object->_image_mapper->get_primary_key_column();
		if (is_object($image_obj_or_id)) {
			if (isset($image_obj_or_id->$image_key)) {
				$retval = $image_obj_or_id->$image_key;
			}
		}
		elseif (is_numeric($image_obj_or_id)) {
			$retval = $image_obj_or_id;
		}

		return $retval;
	}


	/**
	 * Uploads base64 file to a gallery
	 * @param int|stdClass|C_NextGEN_Gallery $gallery
	 * @param $data base64-encoded string of data representing the image
	 * @param type $filename specifies the name of the file
	 * @return C_NextGen_Gallery_Image
	 */
	function upload_base64_image($gallery, $data, $filename=FALSE)
	{
		$retval		= NULL;
		if (($gallery_id = $this->object->_get_gallery_id($gallery))) {

			// Ensure that there is capacity available
			if ( (is_multisite()) && nggWPMU::wpmu_enable_function('wpmuQuotaCheck')) {
				if (upload_is_user_over_quota(FALSE)) {
					throw new E_NoSpaceAvailableException();
				}
			}

			// Get path information. The use of get_upload_abspath() might
			// not be the best for some drivers. For example, if using the
			// WordPress Media Library for uploading, then the wp_upload_bits()
			// function should perhaps be used
			$upload_dir = $this->object->get_upload_abspath($gallery);
			$filename = $filename ? $filename : uniqid('nextgen-gallery');
			$abs_filename = path_join($upload_dir, $filename);

			// Create the database record
			$factory = $this->object->_get_registry()->get_utility('I_Component_Factory');
			$retval = $image = $factory->create('gallery_image');
			$image->alttext		= sanitize_title($filename);
			$image->galleryid	= $this->object->_get_gallery_id($gallery);
			$image->filename	= $filename;
			$image_key			= $this->object->_image_mapper->get_primary_key_column();

			// Save the image
			if ($this->object->_image_mapper->save($image)) {

				try {
					// Try writing the image
					if (!file_exists($upload_dir)) wp_mkdir_p($upload_dir);
					$fp = fopen($abs_filename, 'w');
					fwrite($fp, $data);
					fclose($fp);

					// Determine the dimensions of the image
					// We're going to use a GD function here. There's probably a better
					// way. But WordPress uses GD, so I figure we might as well too.
					// Alex Rabe also said that he's not sure how robust the imagemagick
					// support is, as not many people use it.
					if (function_exists('getimagesize')) {
						$dimensions = getimagesize($abs_filename);
						if (!isset($image->meta_data)) $image->meta_data = array();
						$image->meta_data['full'] = array(
							'width'		=>	$dimensions[0],
							'height'	=>	$dimensions[1]
						);
					}

					// Generate a thumbnail for the image
					$this->object->generate_thumbnail($image);

					// Notify other plugins that an image has been added
					do_action('ngg_added_new_image', $image);

					// delete dirsize after adding new images
					delete_transient( 'dirsize_cache' );

					// Seems redundant to above hook. Maintaining for legacy purposes
					do_action(
						'ngg_after_new_images_added',
						$gallery_id,
						array($image->$image_key)
					);
				}
				catch(Exception $ex) {
					throw new E_InsufficientWriteAccessException();
				}
			}
		}
		else throw new E_InvalidEntityException();

		return $retval;
	}
}

class C_GalleryStorage_Driver_Base extends C_Component
{
    public static $_instances = array();

	function define()
	{
		$this->add_mixin('Mixin_GalleryStorage_Driver_Base');
		$this->implement('I_GalleryStorage_Driver');
	}

	function initialize($context)
	{
		parent::initialize($context);
		$this->_gallery_mapper = $this->_get_registry()->get_utility('I_Gallery_Mapper');
		$this->_image_mapper = $this->_get_registry()->get_utility('I_Gallery_Image_Mapper');
	}

    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_GalleryStorage_Driver_Base($context);
        }
        return self::$_instances[$context];
    }


	/**
	 * Gets the class name of the driver used
	 * @return string
	 */
	function get_driver_class_name()
	{
		return get_called_class();
	}


/**
	 * Gets the url or path of an image of a particular size
	 * @param string $method
	 * @param array $args
	 */
	function __call($method, $args)
	{
		if (preg_match("/^get_(\w+)_(abspath|url|dimensions|html)$/", $method, $match)) {
			if (isset($match[1]) && isset($match[2]) && strpos($method, 'get_image') == FALSE) {
				$method = 'get_image_'.$match[2];
				$args[] = $match[1]; // array($image, $size)
				return parent::__call($method, $args);
//				return call_user_func_array(array(&$this, $method), $args);
			}
		}
		return parent::__call($method, $args);
	}
}
