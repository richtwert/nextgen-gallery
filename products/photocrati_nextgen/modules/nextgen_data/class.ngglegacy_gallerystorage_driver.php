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
		$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');
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
			if (is_numeric($gallery)) {
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
	function get_image_abspath($image, $size='full', $check_existance=FALSE)
	{
		$retval = NULL;

		// If we have the id, get the actual image entity
		if (is_numeric($image)) {
			$image = $this->object->_image_mapper->find($image);
		}

		// Ensure we have the image entity - user could have passed in an
		// incorrect id
		if (is_object($image)) {
			if (($gallery_path = $this->object->get_gallery_abspath($image->galleryid))) {
				$folder = $size;
				switch ($size) {

					# Images are stored in the associated gallery folder
					case 'full':
					case 'original':
						$retval = path_join($gallery_path, $image->filename);
						break;

					case 'thumbnails':
					case 'thumbnail':
					case 'thumb':
					case 'thumbs':
						$size = 'thumbnail';
						$folder = 'thumbs';
						// deliberately no break here

					// We assume any other size of image is stored in the a
					//subdirectory of the same name within the gallery folder
					// gallery folder, but with the size appended to the filename
					default:
						$image_path = path_join($gallery_path, $folder);

						// NGG 2.0 stores relative filenames in the meta data of
						// an image. It does this because it uses filenames
						// that follow conventional WordPress naming scheme.
						if (isset($image->meta_data) && isset($image->meta_data[$size]) && isset($image->meta_data[$size]['filename'])) {
							$image_path = path_join($image_path, $image->meta_data[$size]['filename']);
						}

						// NGG Legacy does not store relative filenames in the
						// image entity for sizes other than the original.
						// Although the naming scheme for filenames differs from
						// WordPress conventions, NGG legacy does follow it's
						// own naming schema consistently so we can guess the path
						else {
							$image_path = path_join($image_path, "{$size}_{$image->filename}");
						}

						// Should we check whether the image actually exists?
						if ($check_existance && file_exists($image_path)) {
							$retval = $image_path;
						}
						elseif (!$check_existance) $retval = $image_path;
						break;
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
			site_url().'/',
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

	/**
	 * Generates a thumbnail for an image
	 * @param int|stdClass|C_NextGen_Gallery_Image $image
	 * @return bool
	 */
	function generate_thumbnail($image, $width=NULL, $height=NULL, $crop=NULL, $quality=NULL)
	{
		$retval = FALSE;

		// Get the image entity
		if (is_numeric($image)) $image = $this->object->_image_mapper->find($image);

		// Ensure we have a valid image
		if ($image) {

			// Get the image filename
			$filename = $this->object->get_full_abspath($image);

			// Get the thumbnail settings
			$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');

			// Generate the thumbnail using WordPress
			$existing_thumbnail_abpath = $this->object->get_thumbnail_abspath($image);
			$thumbnail_dir = dirname($existing_thumbnail_abpath);
			if (file_exists($existing_thumbnail_abpath)) unlink($existing_thumbnail_abpath);
			wp_mkdir_p($thumbnail_dir);
			$retval = image_resize(
				$filename,
				is_null($width)		? $settings->thumbwidth		: $width,
				is_null($height)	? $settings->thumbheight	: $height,
				is_null($crop)		? $settings->thumbfix		: $crop,
				NULL, // filename suffix
				$thumbnail_dir,
				is_null($quality)	? $settings->thumbquality	: $quality
			);

			// We successfully generated the thumbnail
			if (is_string($retval) && file_exists($retval)) {
				if (function_exists('getimagesize')) {
					$dimensions = getimagesize($retval);
					if (!isset($image->meta_data)) $image->meta_data = array();
					$image->meta_data['thumbnail'] = array(
						'width'		=>	$dimensions[0],
						'height'	=>	$dimensions[1],
						'filename'	=>	$retval,
						'generated'	=> microtime()
					);
				}

				$retval = $this->object->_image_mapper->save($image);
				$retval = is_numeric($retval) && $retval > 0 ? TRUE : FALSE;
			}

			// Something went wrong. Thumbnail generation failed!
			else $retval = FALSE;

		}

		return $retval;
	}


	function delete_image($image, $size=FALSE)
	{
		$retval = FALSE;

		// Ensure that we have the image entity
		if (is_numeric($image)) $image = $this->object->_image_mapper->find($image);

		if ($image) {

			// Delete only a particular image size
			if ($size) {
				$abspath = $this->object->get_image_abspath($image, $size);
				if ($abspath && file_exists($abspath)) unlink($abspath);
				if (isset($image->meta_data) && isset($image->meta_data[$size])) {
					unset($image->meta_data[$size]);
					$this->object->_image_mapper->save($image);
				}
			}

			// Delete all sizes of the image
			else {
				// Get the paths to all images
				$abspaths = array($this->get_full_abspath($image));
				if (isset($image->meta_data)) foreach (array_keys($image) as $size) {
					$abspaths[] = $this->object->get_image_abspath($image, $size);
				}

				// Delete each image
				foreach ($abspaths as $abspath)
					if ($abspath && file_exists($abspath)) unlink($abspath);

				// Delete the entity
				$this->object->_image_mapper->destroy($image);
			}
			$retval = TRUE;
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
}
