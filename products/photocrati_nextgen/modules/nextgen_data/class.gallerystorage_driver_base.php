<?php

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

		// Image dimensions are stored in the $image->meta_data
		// property for all implementations
		if ($image) {

			// Ensure that we have an image object
			if (is_int($image)) {
				$image = $this->object->_image_mapper->find($image);
			}

			// Get the dimensions
			if ($size == 'original') $size = 'full';
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
		return "";

		if ($image) {
			// Get the image
			if (is_int($image)) $this->object->_get_image_mapper->find($image);

			// Get the image properties
			$alttext = esc_attr($image->alttext);
			$title	 = esc_attr($image->title);

			// Get the dimensions
			$dimensions = $this->object->_get_image_dimensions($image, $size);

			// Get the image url
			$image_url = $this->object->get_image_url($image, $size);

			$retval = implode(' ', array(
				'<img',
				"alt=\"{$alttext}\"",
				"title=\"{$title}\"",
				"url=\"{$image_url}\"",
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

		if (($image_path = $this->object->get_image_path($image))) {
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
					if (is_int($image)) $this->object->_image_mapper($image);
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
		elseif(is_int($gallery_obj_or_id)) {
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
		elseif (is_int($image_obj_or_id)) {
			$retval = $image_obj_or_id;
		}

		return $retval;
	}

//
//
//	/**
//	 * Returns the filename of the image
//	 * @param stdObject|stdClass|C_DataMapper_Model $image
//	 * @param string $size
//	 * @return string
//	 */
//	function get_image_path($image, $size='original')
//	{
//		return $this->object->_get_image_property($image, 'path', $size);
//	}
//
//	/**
//	 * Uploads an image for a particular gallery. Excepts $_FILES to look like:
//	 *
//	 * Array(
//		 [file]	=>	Array (
//            [name] => Canada_landscape4.jpg
//            [type] => image/jpeg
//            [tmp_name] => /private/var/tmp/php6KO7Dc
//            [error] => 0
//            [size] => 64975
//         )
//	   )
//	 * @param int $gallery_id
//	 * @return C_NextGen_Gallery_Image
//	 * @throws Exception
//	 */
//	function upload_image($gallery_id)
//	{
//		$retval = FALSE;
//
//		// Ensure that there is enough space first
//        require_once(implode(DIRECTORY_SEPARATOR, array(ABSPATH, 'wp-admin', 'includes', 'ms.php')));
//        if ( (is_multisite()) && nggWPMU::wpmu_enable_function('wpmuQuotaCheck')) {
//			if(($error = upload_is_user_over_quota( FALSE ) )) {
//                $retval = FALSE;
//                delete_transient('dirsize_cache');
//                throw new Exception(_("Sorry, you have used your space allocation. Please delete some files to upload more files"));
//			}
//		}
//
//		// Has an image been uploaded ?
//		if (isset($_FILES['file'])) {
//			$img = $_FILES['file'];
//
//			// TODO: Check if it's a zip and the user has the capabilities
//			// of uploading a zip
//
//			// Was there a problem uploading?
//			if ($img['error']) {
//                $error_msg = _("There was a problem uploading the image, ").
//                    (isset($img['name'])? $img['name'] : _('unknown filename')).'. ';
//                if (in_array($img[error], array(1,2))) {
//                    $error_msg .= "The file exceeded the maximum size allowed.";
//                }
//                throw new Exception($error_msg);
//            }
//
//			// Get the upload directory
//			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');
//			if (($upload_dir = $mapper->get_gallery_path($gallery_id))) {
//				$image_path = path_join($upload_dir, str_replace(' ', '_', $img['name']));
//				$image_path = apply_filters('ngg_pre_add_new_image', $image_path, $gallery_id);
//
//				// Create the image record
//				$factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory', 'imported_image');
//				$path_parts = pathinfo( $image_path );
//				$alt_text = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
//				$gallery_image = $factory->create('gallery_image', array(
//					'filename'   => basename($image_path),
//					'galleryid'  => $gallery_id,
//					'alttext'    => $alt_text
//				), NULL, 'imported_image');
//				unset($factory);
//
//				// If everything is good...
//				$gallery_image->validate();
//				if ($gallery_image->is_valid()) {
//
//					// Create the storage directory incase it doesn't exist already
//					if (!file_exists($upload_dir)) wp_mkdir_p($upload_dir);
//
//					// Store the image in the gallery directory
//					if (!move_uploaded_file($img['tmp_name'], $image_path)) {
//						throw new Exception(_("Could not store the image. Please check directory permissions and try again."));
//					}
//
//					// Save the image to the database
//					$gallery_image->save();
//					$retval = $gallery_image;
//
//					// Notify other plugins that an image has been added
//					do_action('ngg_added_new_image', $gallery_image);
//
//					// delete dirsize after adding new images
//					delete_transient( 'dirsize_cache' );
//
//					// Seems redundant to above hook. Maintaining for legacy purposes
//					do_action(
//						'ngg_after_new_images_added',
//						$gallery_id,
//						array($gallery_image->id())
//					);
//
//					//add the preview image if needed
//					// TODO: Using NextGen legacy class. Should provide an instance method
//					// that performs this functionality
//					require_once(path_join(NGGALLERY_ABSPATH, 'admin/functions.php'));
//					nggAdmin::set_gallery_preview ($gallery_id);
//				}
//			}
//		}
//		else throw new Exception(_("No image specified to upload"));
//		return $retval;
//	}
}

class C_GalleryStorage_Driver_Base extends C_Component
{
	function define()
	{
		$this->add_mixin('Mixin_GalleryStorage_Driver_Base');
		$this->implement('I_GalleryStorage_Driver');
	}

	function initialize($context)
	{
		parent::initialize($context);
		$this->_options = $this->_get_registry()->get_utility('I_Photocrati_Options');
		$this->_gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
		$this->_image_mapper = $this->_get_registry()->get_utility('I_Gallery_Image_Mapper');
	}

}