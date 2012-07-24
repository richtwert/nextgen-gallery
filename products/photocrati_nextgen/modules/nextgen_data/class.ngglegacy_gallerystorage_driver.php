<?php

class Mixin_NggLegacy_GalleryStorage_Driver extends Mixin
{
	/**
	 * Returns the named sizes available for images
	 * @return array
	 */
	function get_image_sizes()
	{
		return array('full');
	}


	function get_upload_abspath($gallery=FALSE)
	{
		// Base upload path
		$settings = $this->object->_get_registry()->get_singleton_utility('I_NextGEN_Settings');
		$retval = $settings->get('gallerypath');

		// If a gallery has been specified, then we'll
		// append the ID
		if ($gallery && (($gallery_id = $this->object->_get_gallery_id($gallery)))) {
			$retval = path_join($retval, $gallery_id);
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

		// If a gallery has been specified, then we'll
		// append the ID
		if ($gallery && (($gallery_id = $this->object->_get_gallery_id($gallery)))) {
			$retval = path_join($retval, $gallery_id);
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


	/**
	 * Gets the absolute path where the image is stored
	 * Can optionally return the path for a particular sized image
	 */
	function get_image_abspath($image, $size='full')
	{
		$retval = NULL;

		// Get the image id
		if ($image && (($image_id = $this->object->_get_image_id($image)))) {

			// Get the gallery path associated with the image
			$image = $this->object->_image_mapper->find($image_id);
			if ($image) {
				if (($gallery_path = $this->object->get_gallery_abspath($image->galleryid))) {
					switch ($size) {

						# Images are stored in the associated gallery folder
						case 'full':
						case 'original':
							$retval = path_join($gallery_path, $image->filename);
							break;

						# We assume any other size of image is stored in the a
						# subdirectory of the same name within the gallery folder
						# gallery folder, but with the size appended to the filename
						default:
							$image_path = path_join($gallery_path, $size);
							$image_path = path_join($image_path, $image->filename);
							if (file_exists($image_path)) $retval = $image_path;
							break;
					}
				}
			}
		}

		return $retval;
	}


	/**
	 * Gets the url of a particular-sized image
	 * @param int|object $image
	 * @param string $size
	 * @returns array
	 */
	function get_image_url($image, $size='full')
	{
		return str_replace(
			ABSPATH,
			site_url(),
			$this->object->get_image_abspath($image, $size)
		);
	}

	/**
	 * Uploads an image for a particular gallerys
	 * @param int|stdClass|C_NextGEN_Gallery $gallery
	 * @param type $filename, specifies the name of the file
	 * @param type $data if specified, expects base64 encoded string of data
	 */
	function upload_image($gallery, $filename=FALSE, $data=FALSE)
	{
		// Ensure that we have the data present that we require
		if ((isset($_FILE['file']) && $_FILE['file']['error'] == 0)) {

			//		$_FILES = Array(
			//		 [file]	=>	Array (
			//            [name] => Canada_landscape4.jpg
			//            [type] => image/jpeg
			//            [tmp_name] => /private/var/tmp/php6KO7Dc
			//            [error] => 0
			//            [size] => 64975
			//         )
			//
			$file = $_FILE['file'];
			$this->object->upload_base64_image(
				$gallery_id,
				$filename ? $filename : (isset($file['name']) ? $file['name'] : FALSE),
				file_get_contents($file['tmp_name'])
			);
		}
		elseif ($data) {
			$this->object->upload_base64_image(
				$filename,
				$data
			);
		}
		else throw new E_UploadException();
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
