<?php

class Mixin_Gallery_Image_Mapper_Meta extends Mixin
{
	/**
	 * Gets dimensions for a particular size of the image
	 * @return array
	 */
	function get_image_dimensions($image, $size='thumbs')
	{
		$retval = array();

		switch ($size) {
			case 'thumbs':
			case 'thumbnails':
				if (property_exists($image, 'meta_data') && isset($image->meta_data['thumbnail'])) {
					$retval = $image->meta_data['thumbnail'];
				}
				break;
				case 'original':
			case 'full':
				if (property_exists($image, 'meta_data')) {
					if (isset($image->meta_data['width']))
						$retval['width'] = $image->meta_data['width'];
					if (isset($image->meta_data['height']))
						$retval['height'] = $image->meta_data['height'];
				}
				break;
		}

		return $retval;
	}

	/**
	 * Gets dimensions for the thumbnail image
	 * @return array
	 */
	function get_thumbnail_dimensions($image)
	{
		return $this->object->get_image_dimensions('thumbs');
	}


	/**
	 * Gets the original image dimensions
	 * @return array
	 */
	function get_original_dimensions($image)
	{
		return $this->object->get_image_dimensions('original');
	}
}