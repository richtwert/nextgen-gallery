<?php

class Mixin_NggLegacy_GalleryStorage_Driver extends Mixin
{
	/**
	 * Gets the name of sizes available for the image
	 * @return array
	 */
	function get_image_sizes()
	{
		return array('thumbnail', 'full', 'original');
	}

	/**
	 * Gets the base path for uploads
	 * @return string
	 */
	function get_upload_path($gallery_id=FALSE, $thumbs=FALSE)
	{
		$options = $this->object->_get_registry()->get_singleton_utility('I_Photocrati_Options');
		return $options->storage_dir;
	}


	function get_thumbnail_upload_path($gallery_id)
	{
		return $this->get_upload_path($gallery_id, TRUE);
	}

	/**
	 * Returns the url to an image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @param string $size
	 * @return string
	 */
	function get_image_url($image, $size='original')
	{
		return $this->object->_get_image_property($image, 'path', $size);
	}


	/**
	 * Gets the original image path
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_original_path($image)
	{
		$retval = '';

		if (property_exists($image, 'filename') && $image->filename) {
			if (property_exists($image, 'galleryid' && $image->galleryid)) {
				if (($gallery_path = $this->object->_get_gallery_path($image->galleryid))) {
					$retval = path_join($gallery_path, $image->filename);
				}
			}
		}

		return $retval;
	}


	/**
	 * Gets the url of the original sized image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_original_url($image)
	{
		return $this->object->_to_url($this->object->get_original_path($image));
	}

	/**
	 * Gets the path to the full (original) sized image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_full_path($image)
	{
		return $this->object->get_original_path($image);
	}


	/**
	 * Gets the url of the full (original) sized image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_full_url($image)
	{
		return $this->object->get_original_url($image);
	}


	/**
	 * Gets the path of the thumbnail sized image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_thumbnail_path($image)
	{
		$retval = '';

		if (property_exists($image, 'filename') && $image->filename) {
			if (property_exists($image, 'galleryid') && $image->galleryid) {
				if (($gallery_path = $this->object->_get_gallery_path($image->galleryid))) {
					$retval = path_join(
						$gallery_path,
						path_join('thumbs', 'thumbs_'.$image->filename)
					);
				}
			}
		}

		return $retval;
	}


	/**
	 * Gets the url to the thumbnail image
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return string
	 */
	function get_thumbnail_url($image)
	{
		return $this->object->_to_url($this->object->get_thumbnail_path($image));
	}


	/**
	 * Gets the upload directory for the image
	 * @param int $gallery_id
	 * @return string
	 */
	function _get_upload_directory($gallery_id)
	{
		return $this->_get_gallery_path($gallery_id);
	}
}

class C_NggLegacy_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_NggLegacy_GalleryStorage_Driver');
	}
}
