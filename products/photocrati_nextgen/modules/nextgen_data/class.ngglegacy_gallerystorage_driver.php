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
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
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

        // Ensure that we have a size
		if (!$size) {
			$size = 'full';
		}

		// If we have the id, get the actual image entity
		if (is_numeric($image)) {
			$image = $this->object->_image_mapper->find($image);
		}

		// Ensure we have the image entity - user could have passed in an
		// incorrect id
		if (is_object($image)) {
			if (($gallery_path = $this->object->get_gallery_abspath($image->galleryid))) {
				$folder = $prefix = $size;
				switch ($size) {

					# Images are stored in the associated gallery folder
					case 'full':
					case 'original':
					case 'image':
						$retval = path_join($gallery_path, $image->filename);
						break;

					case 'thumbnails':
					case 'thumbnail':
					case 'thumb':
					case 'thumbs':
						$size = 'thumbnail';
						$folder = 'thumbs';
						$prefix = 'thumbs';
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
							$image_path = path_join($image_path, "{$prefix}_{$image->filename}");
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
	 * @return C_Image
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
	 * Generates a "clone" for an existing image, the clone can be altered using the $params array
	 * @param int|stdClass|C_Image $image
	 * @param array $params
	 * @return object
	 */
	function generate_image_clone($image_path, $clone_path, $params)
	{
		$width = isset($params['width']) ? $params['width'] : null;
		$height = isset($params['height']) ? $params['height'] : null;
		$quality = isset($params['quality']) ? $params['quality'] : null;
		$crop = isset($params['crop']) ? $params['crop'] : null;
		$watermark = isset($params['watermark']) ? $params['watermark'] : null;
		$reflection = isset($params['reflection']) ? $params['reflection'] : null;
		$crop_frame = isset($params['crop_frame']) ? $params['crop_frame'] : null;
		$destpath = null;
		$thumbnail = null;
		
		// XXX this should maybe be removed and extra settings go into $params?
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		// Ensure we have a valid image
		if ($image_path && file_exists($image_path) && strtolower($image_path) != strtolower($clone_path))
		{
			// Ensure target directory exists, but only create 1 subdirectory
			$image_dir = dirname($image_path);
			$clone_dir = dirname($clone_path);
			
			if (!file_exists($clone_dir))
			{
				if (strtolower(realpath($image_dir)) != strtolower(realpath($clone_dir)))
				{
					if (strtolower(realpath($image_dir)) == strtolower(realpath(dirname($clone_dir))))
					{
						wp_mkdir_p($clone_dir);
					}
				}
			}
		
			$image_extension = pathinfo($image_path, PATHINFO_EXTENSION);
			$clone_extension = pathinfo($clone_path, PATHINFO_EXTENSION);
			
			if ($image_extension != null)
			{
				$image_extension = '.' . $image_extension;
			}
			
			if ($clone_extension != null)
			{
				$clone_extension = '.' . $clone_extension;
			}
			
			$image_basename = basename($image_path, $image_extension);
			$clone_basename = basename($clone_path, $clone_extension);
			$clone_suffix = $clone_basename;
			
			if (strpos($clone_basename, $image_basename) !== false)
			{
				$clone_suffix = substr($clone_basename, strlen($image_basename));
			}
			
			if ($clone_suffix[0] == '-')
			{
				// WordPress adds '-' on its own
				$clone_suffix = substr($clone_suffix, 1);
			}
			
			$dimensions = null;
		
			if (function_exists('getimagesize')) {
				$dimensions = getimagesize($image_path);
			}
			
			if ($width == null || $height == null) {
				if ($dimensions != null) {
					
					if ($width == null) {
						$width = $dimensions[0];
					}
			
					if ($height == null) {
						$height = $dimensions[1];
					}
				}
				else {
					// XXX Don't think there's any other option here but to fail miserably...use some hard-coded defaults maybe?
					return null;
				}
			}
			
			if ($dimensions != null) {
				if ($width > $dimensions[0]) {
					$width = $dimensions[0];
				}
				
				if ($height > $dimensions[1]) {
					$height = $dimensions[1];
				}
			}
				
		  if ($crop_frame == null || !$crop)
		  {
				$destpath = image_resize(
						$image_path,
						$width, $height, $crop,
						$clone_suffix, // filename suffix
						$clone_dir,
						$quality
				);
		  }
		  else 
		  {
				$destpath = $clone_path;
				$thumbnail = new C_NggLegacy_Thumbnail($image_path, true);
				
				if ($crop)
				{
					$algo = 'shrink'; // either 'adapt' or 'shrink'

					$crop_x = (int) round($crop_frame['x']);
					$crop_y = (int) round($crop_frame['y']);
					$crop_width = (int) round($crop_frame['width']);
					$crop_height = (int) round($crop_frame['height']);
					$crop_final_width = (int) round($crop_frame['final_width']);
					$crop_final_height = (int) round($crop_frame['final_height']);

					$crop_factor_x = $crop_width / $crop_final_width;
					$crop_factor_y = $crop_height / $crop_final_height;

					if ($algo == 'adapt')
					{
						$crop_width = (int) round($width * $crop_factor_x);
						$crop_height = (int) round($height * $crop_factor_y);
					}

					$crop_diff_x = (int) round(($crop_width - $width) / 2);
					$crop_diff_y = (int) round(($crop_height - $height) / 2);

					$crop_x += $crop_diff_x;
					$crop_y += $crop_diff_y;

					if ($algo == 'shrink')
					{
						$crop_width = $width;
						$crop_height = $height;
					}

					$thumbnail->crop($crop_x, $crop_y, $crop_width, $crop_height);
				}
			
				// Just constraint dimensions to ensure there's no stretching or deformations
				list($width, $height) = wp_constrain_dimensions($dimensions[0], $dimensions[1], $width, $height);
				
				$thumbnail->resize($width, $height);
		  }

			// We successfully generated the thumbnail
			if (is_string($destpath) && (file_exists($destpath) || $thumbnail != null)) 
			{
				if (is_null($thumbnail))
				{
					$thumbnail = new C_NggLegacy_Thumbnail($destpath, true);
				}
				else
				{
					$thumbnail->filename = $destpath;
				}
				
				if ($watermark == 1 || $watermark === true)
				{
					if (in_array($settings->wmType, array('image', 'text')))
					{
						$watermark = $settings->wmType;
					}
					else
					{
						$watermark = 'text';
					}
				}

				if ($watermark == 'image')
				{
					$thumbnail->watermarkImgPath = $settings['wmPath'];
					$thumbnail->watermarkImage($settings['wmPos'], $settings['wmXpos'], $settings['wmYpos']); 
				}
				else if ($watermark == 'text')
				{
					$thumbnail->watermarkText = $settings['wmText'];
					$thumbnail->watermarkCreateText($settings['wmColor'], $settings['wmFont'], $settings['wmSize'], $settings['wmOpaque']);
					$thumbnail->watermarkImage($settings['wmPos'], $settings['wmXpos'], $settings['wmYpos']);  
				}

				if ($reflection)
				{
					$thumbnail->createReflection(40, 40, 50, FALSE, '#a4a4a4');
				}

				$thumbnail->save($destpath, $quality);
			}
		}

		return $thumbnail;
	}
	
	/**
	 * Generates a specific size for an image
	 * @param int|stdClass|C_Image $image
	 * @return bool
	 */
	function generate_image_size($image, $size, $params = null, $skip_defaults = false)
	{
		$retval = FALSE;

		// Get the image entity
		if (is_numeric($image)) {
			$image = $this->object->_image_mapper->find($image);
		}

		// Ensure we have a valid image
		if ($image) 
		{
			$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
			
			if (!$skip_defaults)
			{
				// Get default settings
				if (!isset($params['crop'])) {
					$params['crop'] = $settings->thumbfix;
		        }

				if (!isset($params['quality'])) {
					$params['quality'] = $settings->thumbquality;
				}

				if (!isset($params['watermark'])) {
					$params['watermark'] = $settings->wmType;
				}
			}
			
			// width and height when omitted make generate_image_clone create a clone with original size, so try find defaults regardless of $skip_defaults
			if (!isset($params['width']) || !isset($params['height'])) {
				// First try to search if this size was already created
				if (isset($image->meta_data) && isset($image->meta_data[$size])) {
					$dimensions = $image->meta_data[$size];
					
					if (!isset($params['width'])) {
						$params['width'] = $dimensions['width'];
					}
			
					if (!isset($params['height'])) {
						$params['height'] = $dimensions['height'];
					}
				}
				// if search fails try the 2 default built-in sizes, first thumbnail...
				else if ($size == 'thumbnail') {
					if (!isset($params['width'])) {
						$params['width'] = $settings->thumbwidth;
					}
			
					if (!isset($params['height'])) {
						$params['height'] = $settings->thumbheight;
					}
				}
				// ...and then full, which is the size specified in the global resize options
				else if ($size == 'full') {
					if (!isset($params['width'])) {
						if ($settings->imgAutoResize) {
							$params['width'] = $settings->imgWidth;
						}
					}
			
					if (!isset($params['height'])) {
						if ($settings->imgAutoResize) {
							$params['height'] = $settings->imgHeight;
						}
					}
				}
			}

			// Get the image filename
			$filename = $this->object->get_original_abspath($image, 'original');
			$thumbnail = null;
			
			if ($size == 'full' && $settings->imgBackup == 1) {
				// XXX change this? 'full' should be the resized path and 'original' the _backup path
				$backup_path = $this->object->get_backup_abspath($image);
				
				if (!file_exists($backup_path))
				{
					@copy($filename, $backup_path);
				}
			}
		
			if (!isset($params['crop_frame'])) {
				if (isset($image->meta_data[$size]['crop_frame'])) {
					$params['crop_frame'] = $image->meta_data[$size]['crop_frame'];
					
					if (!isset($params['crop_frame']['final_width'])) {
						$params['crop_frame']['final_width'] = $image->meta_data[$size]['width'];
					}
				
					if (!isset($params['crop_frame']['final_height'])) {
						$params['crop_frame']['final_height'] = $image->meta_data[$size]['height'];
					}
				}
			}
			else {
				if (!isset($params['crop_frame']['final_width'])) {
					$params['crop_frame']['final_width'] = $params['width'];
				}
				
				if (!isset($params['crop_frame']['final_height'])) {
					$params['crop_frame']['final_height'] = $params['height'];
				}
			}

			// Generate the thumbnail using WordPress
			$existing_image_abpath = $this->object->get_image_abspath($image, $size);
			$existing_image_dir = dirname($existing_image_abpath);

			// removing the old thumbnail is actually not needed as generate_image_clone() will replace it
            if (file_exists($existing_image_abpath)) {
                unlink($existing_image_abpath);
            }
      
			wp_mkdir_p($existing_image_dir);
			
			$clone_path = $existing_image_abpath;
			$thumbnail = $this->object->generate_image_clone($filename, $clone_path, $params);
			
			// We successfully generated the thumbnail
			if ($thumbnail != null)
			{
				$clone_path = $thumbnail->fileName;
				
				if (function_exists('getimagesize')) 
				{
					$dimensions = getimagesize($clone_path);
				}
				else
				{
					$dimensions = array($params['width'], $params['height']);
				}
				
				if (!isset($image->meta_data)) 
				{
					$image->meta_data = array();
				}
				
				$size_meta = array(
					'width'		=>	$dimensions[0],
					'height'	=>	$dimensions[1],
					'filename'	=>	$clone_path,
					'generated'	=> microtime()
				);
				
				if (isset($params['crop_frame'])) {
					$size_meta['crop_frame'] = $params['crop_frame'];
				}
				
				$image->meta_data[$size] = $size_meta;

				$retval = $this->object->_image_mapper->save($image);

				if ($retval == 0) {
					$retval = false;
				}
				
				if ($retval) {
					$retval = $thumbnail;
				}
			}
			else {
				// Something went wrong. Thumbnail generation failed!
			}
		}

		return $retval;
	}
	
	/**
	 * Generates a thumbnail for an image
	 * @param int|stdClass|C_Image $image
	 * @return bool
	 */
	function generate_thumbnail($image, $params = null, $skip_defaults = false)
	{
		$sized_image = $this->object->generate_image_size($image, 'thumbnail', $params, $skip_defaults);
		
		return $sized_image != null;
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
				if (isset($image->meta_data)) foreach (array_keys($image->meta_data) as $size) {
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

    /**
     * Copies (or moves) images into another gallery
     *
     * @param array $images
     * @param int|object $gallery
     * @param boolean $db optionally only copy the image files
     * @param boolean $move move the image instead of copying
     * @return mixed NULL on failure, array|image-ids on success
     */
    function copy_images($images, $gallery, $db = TRUE, $move = FALSE)
    {
        // return values
        $message        = '';
        $new_image_pids = array();

        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        // move_images() is a wrapper to this function so we implement both features here
        $func = $move ? 'rename' : 'copy';

        // ngg-legacy allows for arrays of just the ID
        if (!is_array($images))
        {
            $images = array($images);
        }

        // Ensure we have a valid gallery
        $gallery_id = $this->object->_get_gallery_id($gallery);
        if (!$gallery_id)
        {
            return;
        }

        $image_key = $this->object->_image_mapper->get_primary_key_column();

        // Check for folder permission
        if (!is_dir($gallery->path) && !wp_mkdir_p($gallery->path))
        {
            $message .= sprintf(__('Unable to create directory %s.', 'nggallery'), esc_html(WINABSPATH . $gallery->path));
            return;
        }
        if (!is_writable(WINABSPATH . $gallery->path))
        {
            $message .= sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'nggallery'), esc_html(WINABSPATH . $gallery->path));
            return;
        }

        foreach ($images as $image) {

            // Ensure that there is capacity available
            if ((is_multisite()) && $settings->get('wpmuQuotaCheck'))
            {
                if (upload_is_user_over_quota(FALSE)) {
                    $message .= sprintf(__('Sorry, you have used your space allocation. Please delete some files to upload more files.', 'nggallery'));
                    throw new E_NoSpaceAvailableException();
                }
            }

            // Copy the db entry
            if (is_numeric($image))
            {
                $image = $this->object->_image_mapper->find($image);
            }
            $old_pid = $image->$image_key;

            if ($db)
            {
                $new_image = clone $image;
                unset($new_image->$image_key);
                $new_image->galleryid = $gallery_id;
                $new_pid = $this->object->_image_mapper->save($new_image);
                $new_image = $this->object->_image_mapper->find($new_image);
            } else {
                $new_pid = $old_pid;
            }

            if (!$new_pid) {
                $message .= sprintf(__('Failed to copy database row for picture %s', 'nggallery'), $old_pid) . '<br />';
                continue;
            }

            $new_image_pids[] = $new_pid;

            // Copy each image size
            foreach ($this->object->get_image_sizes() as $size) {

                $orig_path = $this->object->get_image_abspath($image, $size, TRUE);
                if (!$orig_path)
                {
                    $message .= sprintf(__('Failed to get image path for %s', 'nggallery'), esc_html($image->filename)) . '<br/>';
                    continue;
                }

                $new_path = basename($orig_path);

                $prefix       = '';
                $prefix_count = 0;
                while (file_exists($gallery->path . DIRECTORY_SEPARATOR . $new_path))
                {
                    $prefix = 'copy_' . ($prefix_count++) . '_';
                    $new_path = $prefix . $new_path;
                }
                $new_path = path_join($gallery->path, $new_path);

                // Copy files
                if (!@$func($orig_path, $new_path))
                {
                    $message .= sprintf(__('Failed to copy image %1$s to %2$s', 'nggallery'), esc_html($orig_path), esc_html($new_path)) . '<br/>';
                    continue;
                }
                else {
                    $message .= sprintf(__('Copied image %1$s to %2$s', 'nggallery'), esc_html($orig_path), esc_html($new_path)) . '<br/>';
                }

                // Copy backup file, if possible
                @$func($orig_path . '_backup', $new_path . '_backup');

                if ($prefix != '')
                {
                    $message .= sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s) &raquo; The file already existed in the destination gallery.', 'nggallery'), $old_pid, esc_html($orig_path), $new_pid, esc_html($new_path)) . '<br />';
                }
                else
                {
                    $message .= sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s)', 'nggallery'), $old_pid, esc_html($orig_path), $new_pid, esc_html($new_path)) . '<br />';
                }

                // Copy tags
                if ($db)
                {
                    $tags = wp_get_object_terms($old_pid, 'ngg_tag', 'fields=ids');
                    $tags = array_map('intval', $tags);
                    wp_set_object_terms($new_pid, $tags, 'ngg_tag', true);
                }
            }
        }

        $message .= '<hr />' . sprintf(__('Copied %1$s picture(s) to gallery %2$s .', 'nggallery'), count($new_image_pids), $gallery->title);

        return $new_image_pids;
    }

    /**
     * Recover image from backup copy and reprocess it
     *
     * @param int|stdClass|C_Image $image
     * @return string result code
     */
    function recover_image($image) {

        if (is_numeric($image))
        {
            $image = $this->object->_image_mapper->find($image);
        }

        if (isset($image->meta_data))
        {
            $orig_metadata = $image->meta_data;
        }

        $path = $this->object->get_registry()->get_utility('I_Gallery_Storage')->get_image_abspath($image);

        if (!is_object($image))
        {
            return __("Could not find image", 'nggallery');
        }

        if (!is_writable($path) && !is_writable(dirname($path)))
        {
            return ' <strong>' . esc_html($image->filename) . __(' is not writeable', 'nggallery') . '</strong>';
        }

        if (!file_exists($path . '_backup'))
        {
            return ' <strong>' . __('Backup file does not exist', 'nggallery') . '</strong>';
        }

        if (!@copy($path . '_backup', $path))
        {
            return ' <strong>' . __("Could not restore original image", 'nggallery') . '</strong>';
        }

        if (isset($orig_metadata))
        {
            $NextGen_Metadata = new C_NextGen_Metadata($image);
            $new_metadata = $NextGen_Metadata->get_common_meta();
            $image->meta_data = array_merge((array)$orig_metadata, (array)$new_metadata);
            $this->object->_image_mapper->save($image);
        }

        return '1';
    }
}

class C_NggLegacy_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NggLegacy_GalleryStorage_Driver');
	}
}
