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
		$settings = $this->object->_get_registry()->get_singleton_utility('I_NextGen_Settings');
		$retval = $settings->get('gallerypath');

		// If a gallery has been specified, then we'll
		// append the slug
		if ($gallery) {
			if (is_object($gallery) && isset($gallery->slug)) {
				$retval = path_join($retval, $gallery->slug);
			}
			else {
				$gallery = $this->object->_get_gallery_id($gallery);
				$gallery = $this->object->_gallery_mapper->find($gallery);
				if ($gallery) $retval = path_join($retval, $gallery->slug);
			}
		}

		// We need to make this an absolute path
		if (strpos($retval, ABSPATH) === FALSE)
				$retval = path_join(ABSPATH, $retval);

		return $retval;
	}


	/**
	 * Get the gallery path persisted in the database for the gallery
	 * @param int|stdClass|C_NextGen_Gallery $gallery
	 */
	function get_gallery_abspath($gallery)
	{
		$retval = NULL;

		// Get the gallery entity from the database
		if ($gallery) {
			if (is_int($gallery)) {
				$gallery = $this->object->_gallery_mapper->find($gallery);
			}
		}

		// If a path was stored in the entity, then use that
		if ($gallery && isset($gallery->path)) {
			$retval = path_join(ABSPATH, $gallery->path);
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
	 * @return C_NextGen_Gallery_Image
	 */
	function upload_image($gallery, $filename=FALSE, $data=FALSE)
	{
		$retval = NULL;

		// Ensure that we have the data present that we require
		if ((isset($_FILES['file']) && $_FILES['file']['error'] == 0)) {

			//		$_FILES = Array(
			//		 [file]	=>	Array (
			//            [name] => Canada_landscape4.jpg
			//            [type] => image/jpeg
			//            [tmp_name] => /private/var/tmp/php6KO7Dc
			//            [error] => 0
			//            [size] => 64975
			//         )
			//
			$file = $_FILES['file'];
			$retval = $this->object->upload_base64_image(
				$gallery,
				file_get_contents($file['tmp_name']),
				$filename ? $filename : (isset($file['name']) ? $file['name'] : FALSE)
			);
		}
		elseif ($data) {
			$retval = $this->object->upload_base64_image(
				$filename,
				$data
			);
		}
		else throw new E_UploadException();

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
}
