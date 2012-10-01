<?php

class A_Dynamic_Thumbnails_Storage_Driver extends Mixin
{
	function get_image_abspath($image, $size=FALSE, $check_existance=FALSE)
	{
		$retval = NULL;
		$dynthumbs = $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');

		if ($dynthumbs && $dynthumbs->is_size_dynamic($size))
		{
			// If we have the id, get the actual image entity
			if (is_numeric($image)) {
				$image = $this->object->_image_mapper->find($image);
			}

			// Ensure we have the image entity - user could have passed in an
			// incorrect id
			if (is_object($image)) {
				if (($gallery_path = $this->object->get_gallery_abspath($image->galleryid))) {
					$folder = 'dynamic';
					$folder_path = path_join($gallery_path, $folder);
					$params = $dynthumbs->get_params_from_name($size, true);
					$image_filename = $dynthumbs->get_image_name($image, $params);

					$image_path = path_join($folder_path, $image_filename);

					if ($check_existance)
					{
						if (file_exists($image_path))
						{
							$retval = $image_path;
						}
					}
					else
					{
						$retval = $image_path;
					}
				}
			}
		}
		else  {
			$retval = $this->call_parent('get_image_abspath', $image, $size, $check_existance);
		}

		return $retval;
	}

	function get_image_url($image, $size=FALSE)
	{
		$dynthumbs = $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');

		if ($dynthumbs && $dynthumbs->is_size_dynamic($size))
		{
			$retval = NULL;
			$abspath = $this->object->get_image_abspath($image, $size, true);

			if (!$abspath)
			{
				$params = $dynthumbs->get_params_from_name($size, true);
				$retval = $dynthumbs->get_image_url($image, $params);
			}

			return $retval;
		}

		return $this->call_parent('get_image_url', $image, $size);
	}

	function generate_image_size($image, $size, $width=NULL, $height=NULL, $quality=NULL, $crop=NULL, $watermark=NULL, $reflection=NULL, $return_thumb=false)
	{
		$dynthumbs = $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');

		if ($dynthumbs && $dynthumbs->is_size_dynamic($size))
		{
			$params = $dynthumbs->get_params_from_name($size, true);

			$width = $params['width'];
			$height = $params['height'];
			$quality = $params['quality'];
			$crop = $params['crop'];
			$watermark = $params['watermark'];
			$reflection = $params['reflection'];
		}

		return $this->call_parent('generate_image_size', $image, $size, $width, $height, $quality, $crop, $watermark, $reflection, $return_thumb);
	}
}
