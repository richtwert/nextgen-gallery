<?php

class Hook_NggLegacy_Gallery_Storage extends Hook
{
	// Adds 'thumbnail' as an available image size
	function get_image_sizes()
	{
		// Get the return value
		$prop	= ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE;
		$retval = $this->object->get_method_property(__METHOD__, $prop);

		// Set the return value
		if (!is_array($retval)) $retval = array();
		$this->object->set_method_property(__METHOD__, $prop, $retval);

		return $retval;
	}


	/**
	 * Adds "thumbnail" as a possible image size to the get_image_abspath()
	 * method
	 * @param int|object $image
	 * @param string $size
	 * @return string
	 */
	function get_image_abspath($image=FALSE, $size='full')
	{
		$retval = NULL;

		if (in_array($size,array('thumbnail','thumb','thumbnails','thumbs'))) {
			if ($image) {

				// Get image object
				if (is_int($image)) {
					$image = $this->object->_image_mapper->find($image);
				}

				// Do we have a valid image?
				if ($image) {
					$thumbs_path = $this->object->get_gallery_thumbnail_path(
						$image->galleryid
					);

					// If we have a thumbnail path
					if ($thumbs_path) {

						// Return thumbnail path
						$retval = path_join($thumbs_path, $image->filename);

						$this->object->set_method_property(
							ExtensibleObject::METHOD_PROPERTY_RUN,
							FALSE
						);
						$this->object->set_method_property(
							ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
							$retval
						);
					}
				}
			}
		}

		return $retval;
	}
}

class A_Thumbnail_Gallery_Storage extends Mixin
{
	/**
	 * Adds hooks specific to driver methods
	 */
	function initialize()
	{
		// Add hooks for ngglegacy driver
		$hook_name = 'Add Thumbnail Support';
		$driver = $this->object->_get_driver_factory_method();
		if (strpos($driver, 'ngglegacy') !== FALSE) {

			// Add thumbnail image size
			$this->object->add_post_hook(
				'get_image_sizes',
				$hook_name,
				'Hook_NggLegacy_Gallery_Storage'
			);

			// Handle get_image_abspath() for thumbnails
			$this->object->add_pre_hook(
				'get_image_abspath',
				$hook_name,
				'Hook_NggLegacy_Gallery_Storage'
			);
		}

		// Ensure that the driver meets the new interface
		$this->object->implement('I_Thumbnail_GalleryStorage_Driver');
	}


	/**
	 * Gets the thumbnail path for the particular gallery
	 */
	function get_gallery_thumbnail_abspath($gallery)
	{
		return path_join($this->object->get_gallery_abspath($gallery), 'thumbs');
	}


	/**
	 * Gets the absolute path of the thumbnail image
	 * @param int|object $image
	 */
	function get_thumbnail_abspath($image)
	{
		return $this->object->get_image_abspath($image, 'thumbnail');
	}

	/**
	 * Alias for get_thumbnail_abspath()
	 * @param int|object $image
	 */
	function get_thumbs_abspath($image)
	{
		return $this->object->get_thumbnail_abspath($image);
	}

	/**
	 * Alias to get_image_dimensions()
	 * @param int|object $image
	 * @return array
	 */
	function get_thumbs_dimensions($image)
	{
		return $this->object->get_image_dimensions($image, 'thumbnail');
	}


	/**
	 * Alias to get_image_dimensions()
	 * @param int|object $image
	 * @return array
	 */
	function get_thumbnail_dimensions($image)
	{
		return $this->object->_get_image_dimensions($image, 'thumbnail');
	}


	/**
	 * Alias to get_image_html()
	 * @param int|object $image
	 * @return string
	 */
	function get_thumbnail_html($image)
	{
		return $this->object->get_image_html($image, 'thumbnail');
	}


	/**
	 * Alias to get_thumbnail_html()
	 * @param int|object $image
	 * @return string
	 */
	function get_thumbs_html($image)
	{
		return $this->object->get_image_html($image, 'thumbnail');
	}


	/**
	 * Creates a new thumbnail of a particular size
	 * @param int|object $image
	 * @param string $size
	 */
	function create_thumbnail($image, $size='thumbnail')
	{
		$retval = FALSE;

		// Ensure that we have a valid image id
		if (($image = $this->object->_get_image_id($image))) {

			// Ensure that we have a the thumbnail class
			if(! class_exists('ngg_Thumbnail'))
				require_once( nggGallery::graphic_library() );

			// Generate a new thumbnail based on the original image
			$thumb = new ngg_Thumbnail(
				$this->object->get_image_abspath($image),
				TRUE
			);

			// Were we able to generate a thumbnail successfully?
			// The above is extracted from ngglegacy
			if (!$thumb->error) {

				// Are we to maintain the aspect ratio?
				$options = &$this->object->_options;
				if (isset($options->thumbfix)) {

					// calculate correct ratio
					$wratio = $options->thumbwidth / $thumb->currentDimensions['width'];
					$hratio = $options->thumbheight / $thumb->currentDimensions['height'];

					if ($wratio > $hratio) {
						// first resize to the wanted width
						$thumb->resize($options->thumbwidth, 0);
						// get optimal y startpos
						$ypos = ($thumb->currentDimensions['height'] - $options->thumbheight) / 2;
						$thumb->crop(0, $ypos, $options->thumbwidth,$options->thumbheight);
					} else {
						// first resize to the wanted height
						$thumb->resize(0, $options->thumbheight);
						// get optimal x startpos
						$xpos = ($thumb->currentDimensions['width'] - $options->thumbwidth) / 2;
						$thumb->crop($xpos, 0, $options->thumbwidth,$options->thumbheight);
					}

				//this create a thumbnail but keep ratio settings
				} else {
					$thumb->resize($options->thumbwidth,$options->thumbheight);
				}

				// save the new thumbnail
				$thumb_path = $this->object->get_thumbnail_path($image);
				$thumb->save($thumb_path, $options->thumbquality);
				$this->object->_chmod($image_path);

				//read the new sizes
				$new_size = @getimagesize ( $thumb_path );
				if ($new_size) {
					$thumb_size = array(
						'width'		=> $new_size[0],
						'height'	=> $new_size[1]
					);

					// Update the image metadata with the new thumbnail sizes
					$image = $this->object->_image_mapper->find($image);
					$image->meta_data['thumbnail'] = $size;
				}
			}
			$thumb->destruct();
		}

		return $retval;
	}
}
