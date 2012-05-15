<?php

class Mixin_WordPress_GalleryStorage_Driver extends Mixin
{
	/**
	 * Returns the named sizes available for images
	 * @global array $_wp_additional_image_sizese
	 * @return array
	 */
	function get_image_sizes()
	{
		global $_wp_additional_image_sizes;
		return array_merge(array_keys($_wp_additional_image_sizes), array('full', 'thumbnail'));
	}


	/**
	 * Gets the upload path for new images in this gallery
	 * This will always be the date-based directory
	 * @param type $gallery
	 * @return type
	 */
	function get_upload_abspath($gallery=FALSE)
	{
		// Gallery is used for this driver, as the upload path is
		// the same, regardless of what gallery is used

		$retval = FALSE;

		$dir = wp_upload_dir(time());
		if ($dir) $retval = $dir['path'];

		return $retval;
	}


	/**
	 * Will always
	 * @param type $gallery
	 */
	function get_gallery_path($gallery=FALSE)
	{

	}


	/**
	 * Handles calls to get_original_path|url, etc
	 * @param string $method
	 * @param array $args
	 * @return string
	 */
	function __call($method, $args)
	{
		$retval = '';

		if (preg_match("/get_(\w+)_(path|url)/", $method, $match)) {
			$id_field = $image->id_field;
			$retval = wp_get_attachment_image_src($image->$id_field, $match[1]);
			if ($match[2] == 'url') $retval = $this->object->_to_url($retval);
		}

		else $retval = parent::__call ($method, $args);

		return $retval;
	}
}

class C_WordPress_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_WordPress_GalleryStorage_Driver');
	}
}