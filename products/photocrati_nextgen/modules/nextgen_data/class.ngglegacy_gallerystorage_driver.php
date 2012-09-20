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
	function generate_thumbnail($image, $width=NULL, $height=NULL, $crop=NULL, $quality=NULL, $watermark=NULL, $reflection=NULL, $return_thumb=false, $combine_filename = FALSE)
	{
		$retval = FALSE;

		// Get the image entity
		if (is_numeric($image)) $image = $this->object->_image_mapper->find($image);

		// Ensure we have a valid image
		if ($image) {

			// Get the thumbnail settings
			$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
			
			if (is_null($width)) {
				$width = $settings->thumbwidth;
			}
			
			if (is_null($height)) {
				$height = $settings->thumbheight;
			}

			if (is_null($crop)) {
				$crop = $settings->thumbfix;
            }

			if (is_null($quality)) {
				$quality = $settings->thumbquality;
			}

			// Get the image filename
			$filename = $this->object->get_full_abspath($image);
			$thumbnail = null;
			$crop_frame = null;
			
			if (isset($image->meta_data) && isset($image->meta_data['thumbnail_crop_frame'])) {
				$crop_frame = $image->meta_data['thumbnail_crop_frame'];
			}

			// Generate the thumbnail using WordPress
			$existing_thumbnail_abpath = $this->object->get_thumbnail_abspath($image);

			// XXX use $filename instead of $existing_thumbnail_abpath here? it would make it compatible with image_resize()
			$thumbnail_info = pathinfo($existing_thumbnail_abpath);
			$thumbnail_dir = $thumbnail_info['dirname'];
			$thumbnail_ext = $thumbnail_info['extension'];
			$thumbnail_name = wp_basename($existing_thumbnail_abpath, ".$thumbnail_ext");

            // singlepic requires cached filenames include all options in the filename
            if (!$combine_filename)
            {
                $thumbnail_suffix = "{$width}x{$height}";
            }
            else {
                $thumbnail_suffix = "{$width}x{$height}_{$crop}_{$quality}_{$reflection}_{$watermark}_{$reflection}";
            }

			// XXX removing the old thumbnail might not be what we want when thumb size is dynamic or passed through shortcode etc.
            // if (file_exists($existing_thumbnail_abpath)) unlink($existing_thumbnail_abpath);
			wp_mkdir_p($thumbnail_dir);

			// only resize the image if we must-we may only be applying an effect like watermarks
            if ($width < $image->meta_data['width'] || $height < $image->meta_data['height'])
            {
                if ($crop_frame == null )
                {
                    $destpath = image_resize(
                        $filename,
                        $width, $height, $crop,
                        ($combine_filename ? $thumbnail_suffix : NULL), // filename suffix
                        $thumbnail_dir,
                        $quality
                    );
                }
                else {
                    $destpath = "{$thumbnail_dir}/{$thumbnail_name}-{$thumbnail_suffix}.{$thumbnail_ext}";
                    $thumbnail = new C_NggLegacy_Thumbnail($filename, true);
                    $algo = 'shrink'; // either 'adapt' or 'shrink'

                    $crop_x = (int) round($crop_frame['x']);
                    $crop_y = (int) round($crop_frame['y']);
                    $crop_width = (int) round($crop_frame['width']);
                    $crop_height = (int) round($crop_frame['height']);
                    $crop_thumbnail_width = (int) round($crop_frame['thumbnail_width']);
                    $crop_thumbnail_height = (int) round($crop_frame['thumbnail_height']);

                    $crop_factor_x = $crop_width / $crop_thumbnail_width;
                    $crop_factor_y = $crop_height / $crop_thumbnail_height;

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
                    $thumbnail->resize($width, $height);
                }
            }
            else {
                // the file needs to exist for effects to be applied to it
                $destpath = "{$thumbnail_dir}/{$thumbnail_name}-{$thumbnail_suffix}.{$thumbnail_ext}";
                if (is_string($destpath) && !file_exists($destpath))
                {
                    copy($filename, $destpath);
                }
            }

			// We successfully generated the thumbnail
			if (is_string($destpath) && (file_exists($destpath) || $thumbnail != null)) {
				
				if (is_null($thumbnail))
                {
					$thumbnail = new C_NggLegacy_Thumbnail($destpath, true);
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
				
				if (function_exists('getimagesize')) {
					$dimensions = getimagesize($destpath);
					// XXX As above...I don't think we want to overwrite these settings in the meta_data as this function could be called for dynamic thumbs and so on
#					if (!isset($image->meta_data)) $image->meta_data = array();
#					$image->meta_data['thumbnail'] = array(
#						'width'		=>	$dimensions[0],
#						'height'	=>	$dimensions[1],
#						'filename'	=>	$destpath,
#						'generated'	=> microtime()
#					);
				}

				$retval = $this->object->_image_mapper->save($image);

				if ($retval == 0) {
					$retval = false;
				}
				
				// XXX adjust this? save() returns false...
				if (/*$retval &&*/ $return_thumb) {
					return $thumbnail;
				}
			}
			else {
				// Something went wrong. Thumbnail generation failed!
			}
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
     * @param int|stdClass|C_NextGen_Gallery_Image $image
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
