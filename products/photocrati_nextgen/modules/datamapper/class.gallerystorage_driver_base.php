<?php

class Mixin_Gallery_Image_Meta extends Mixin
{
	function get_image_html($image, $size='original')
	{
		$retval			= '';
		$dimensions		= FALSE;
		$url			= FALSE;

		// Determine what meta data to collect
		switch($size) {
			case 'original':
			case 'full':
				$dimensions		= $this->object->get_original_dimensions($image);
				$url			= $this->object->get_original_url($image);
				break;
			case 'thumbs':
			case 'thumbnails':
				$dimensions		= $this->object->get_thumbnail_dimensions($image);
				$url			= $this->object->get_thumbnail_url($image);
				break;
		}

		// If we have meta data...
		if ($dimensions && $url) {
			$alttext = property_exists($image, 'alttext') ? $image->alttext : '';
			$desc	 = property_exists($image, 'description') ? $image->description : '';
			$retval = "<img src='".esc_atr($url)."' ".
						   "width='".esc_attr($dimensions['width'])."' ".
					       "height='".esc_attr($dimensions['height'])."' ".
						   "alttext='".esc_attr($alttext)."' ".
						   "title='".esc_attr($desc)."'/>";
		}

		return $retval;
	}

	/**
	 * Gets the IMG HTML tag for the thumbnail image
	 * @param stdObject|stdClass|C_NextGen_Gallery_Image $image
	 */
	function get_thumbnail_html($image)
	{
		$this->object->get_image_html($image, 'thumbs');
	}


	/**
	 * Gets the IMG HTML tag for the original image
	 * @param stdObject|stdClass|C_NextGen_Gallery_Image $image
	 */
	function get_original_html($image)
	{
		$this->object->get_image_html($image, 'original');
	}

	/**
	 * Alias for get_original_html()
	 * @param stdObject|stdClass|C_NextGen_Gallery_Image $image
	 */
	function get_full_html($image)
	{
		$this->object->get_image_html($image, 'original');
	}

	/**
	 * Gets dimensions for a particular size of the image
	 * @return array
	 */
	function get_image_dimensions($image, $size='thumbs')
	{
		$retval = array();

		switch ($size) {
			case 'thumbs':
			case 'thumbnails':
				if (property_exists($image, 'meta_data') && isset($image->meta_data['thumbnail'])) {
					$retval = $image->meta_data['thumbnail'];
				}
				break;
			case 'original':
			case 'full':
				if (property_exists($image, 'meta_data')) {
					if (isset($image->meta_data['width']))
						$retval['width'] = $image->meta_data['width'];
					if (isset($image->meta_data['height']))
						$retval['height'] = $image->meta_data['height'];
				}
				break;
		}

		return $retval;
	}

	/**
	 * Gets dimensions for the thumbnail image
	 * @return array
	 */
	function get_thumbnail_dimensions($image)
	{
		return $this->object->get_image_dimensions($image, 'thumbs');
	}


	/**
	 * Gets the original image dimensions
	 * @return array
	 */
	function get_original_dimensions($image)
	{
		return $this->object->get_image_dimensions($image, 'original');
	}
}

class Mixin_GalleryStorage_Driver_Base extends Mixin
{
	/**
	 * Returns a property (path or url) of an image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @param string $property
	 * @param string $size
	 * @return string
	 */
	function _get_image_property($image, $property, $size)
	{
		$retval = '';

		if (in_array($size, $this->object->get_image_sizes($image))) {
			$retval = call_user_func_array(array($this->object, 'get_'.$size.'_'.$property), array($image, $size));
		}

		return $retval;
	}


	/**
	 * Returns the url equivalent of a filename
	 * @param string $absolute_path
	 * @return string
	 */
	function _to_url($absolute_path)
	{
		return site_url(str_replace("\\", '/', $absolute_path));
	}


	/**
	 * Returns the filename of the image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @param string $size
	 * @return string
	 */
	function get_image_path($image, $size='original')
	{
		return $this->object->_get_image_property($image, 'path', $size);
	}

	/**
	 * Uploads an image for a particular gallery. Excepts $_FILES to look like:
	 *
	 * Array(
		 [file]	=>	Array (
            [name] => Canada_landscape4.jpg
            [type] => image/jpeg
            [tmp_name] => /private/var/tmp/php6KO7Dc
            [error] => 0
            [size] => 64975
         )
	   )
	 * @param int $gallery_id
	 * @return C_NextGen_Gallery_Image
	 * @throws Exception
	 */
	function upload_image($gallery_id)
	{
		$retval = FALSE;

		// Ensure that there is enough space first
        require_once(implode(DIRECTORY_SEPARATOR, array(ABSPATH, 'wp-admin', 'includes', 'ms.php')));
        if ( (is_multisite()) && nggWPMU::wpmu_enable_function('wpmuQuotaCheck')) {
			if(($error = upload_is_user_over_quota( FALSE ) )) {
                $retval = FALSE;
                delete_transient('dirsize_cache');
                throw new Exception(_("Sorry, you have used your space allocation. Please delete some files to upload more files"));
			}
		}

		// Has an image been uploaded ?
		if (isset($_FILES['file'])) {
			$img = $_FILES['file'];

			// Was there a problem uploading?
			if ($img['error']) {
                $error_msg = _("There was a problem uploading the image, ").
                    (isset($img['name'])? $img['name'] : _('unknown filename')).'. ';
                if (in_array($img[error], array(1,2))) {
                    $error_msg .= "The file exceeded the maximum size allowed.";
                }
                throw new Exception($error_msg);
            }

			// Get the upload directory
			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');
			if (($upload_dir = $mapper->get_gallery_path($gallery_id))) {
				$image_path = path_join($upload_dir, str_replace(' ', '_', $img['name']));
				$image_path = apply_filters('ngg_pre_add_new_image', $image_path, $gallery_id);

				// Create the image record
				$factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory', 'imported_image');
				$path_parts = pathinfo( $image_path );
				$alt_text = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
				$gallery_image = $factory->create('gallery_image', array(
					'filename'   => basename($image_path),
					'galleryid'  => $gallery_id,
					'alttext'    => $alt_text
				), NULL, 'imported_image');
				unset($factory);

				// If everything is good...
				$gallery_image->validate();
				if ($gallery_image->is_valid()) {

					// Create the storage directory incase it doesn't exist already
					if (!file_exists($upload_dir)) wp_mkdir_p($upload_dir);

					// Store the image in the gallery directory
					if (!move_uploaded_file($img['tmp_name'], $image_path)) {
						throw new Exception(_("Could not store the image. Please check directory permissions and try again."));
					}

					// Save the image to the database
					$gallery_image->save();
					$retval = $gallery_image;

					// Notify other plugins that an image has been added
					do_action('ngg_added_new_image', $gallery_image);

					// delete dirsize after adding new images
					delete_transient( 'dirsize_cache' );

					// Seems redundant to above hook. Maintaining for legacy purposes
					do_action(
						'ngg_after_new_images_added',
						$gallery_id,
						array($gallery_image->id())
					);

					//add the preview image if needed
					// TODO: Using NextGen legacy class. Should provide an instance method
					// that performs this functionality
					require_once(path_join(NGGALLERY_ABSPATH, 'admin/functions.php'));
					nggAdmin::set_gallery_preview ($gallery_id);
				}
			}
		}
		else throw new Exception(_("No image specified to upload"));
		return $retval;
	}
}

class C_GalleryStorage_Driver_Base extends C_Component
{
	function define()
	{
		$this->add_mixin('Mixin_GalleryStorage_Driver_Base');
		$this->add_mixin('Mixin_Gallery_Image_Meta');
		$this->implement('I_GalleryStorage_Driver');
	}
}