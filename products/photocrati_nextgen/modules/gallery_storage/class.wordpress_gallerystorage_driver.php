<?php

class Mixin_WordPress_GalleryStorage_Driver extends Mixin
{
	/**
	 * Returns the name of available image sizes available
	 * @global array $_wp_additional_image_sizes
	 * @param stdObject|stdClass|C_DataMapper_Model $image
	 * @return array
	 */
	function get_image_sizes($image)
	{
		global $_wp_additional_image_sizes;
		return array_keys($_wp_additional_image_sizes);
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

	/**
	 * Gets the base path for uploads
	 * @return string
	 */
	function get_upload_path()
	{
		$retval = FALSE;

		$dir = wp_upload_dir(time());
		if ($dir) $retval = $dir['path'];

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