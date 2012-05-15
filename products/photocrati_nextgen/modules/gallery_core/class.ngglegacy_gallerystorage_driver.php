<?php

class Mixin_NggLegacy_GalleryStorage_Driver extends Mixin
{
	/**
	 * Returns the named sizes available for images
	 * @return array
	 */
	function get_image_sizes()
	{
		return array('full', 'thumbnail');
	}


	function get_upload_abspath($gallery=FALSE)
	{
		// Base upload path
		$retval = $this->_options->storage_dir;

		// If a gallery has been specified, then we'll
		// append the ID
		if ($gallery) {

			// Get the gallery ID
			$gallery_key = $this->_gallery_mapper->get_primary_key_column();
			if (is_object($gallery) && isset($gallery->$gallery_key)) {
				$gallery = $gallery->$gallery_key;
			}

			// Ensure we have a gallery ID
			if (is_int($gallery)) $retval = path_join($retval, $gallery);
		}

		return $retval;
	}


	/**
	 * Get the gallery path persisted in the database for the gallery
	 * @param type $gallery
	 */
	function get_gallery_abspath($gallery)
	{
		$retval = NULL;

		// Get the gallery ID
		$gallery_key = $this->_gallery_mapper->get_primary_key_column();
		if (is_object($gallery) && isset($gallery->$gallery_key)) {
			$gallery = $gallery->$gallery_key;
		}

		// Ensure that we have a gallery ID
		if (is_int($gallery)) {
			$retval = ABSPATH;

			// Fetch the gallery from the database to ensure we
			// find the latest path
			$gallery_object = $this->_gallery_mapper->find($gallery);
			if ($gallery_object) {

				// If the gallery has an associated path with it,
				// return the absolute path
				if (isset($gallery_object->path)) {
					$retval = path_join(ABSPATH, $gallery_object->path);
				}
			}
		}

		return $retval;
	}

}

class C_NggLegacy_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_NggLegacy_GalleryStorage_Driver');
	}

	function initialize($context)
	{
		parent::initialize($context);
		$this->_options = $this->_get_registry()->get_utility('I_Photocrati_Options');
		$this->_gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
	}
}
