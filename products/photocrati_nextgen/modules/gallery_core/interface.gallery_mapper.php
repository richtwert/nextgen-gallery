<?php

interface I_Gallery_Mapper
{
	/**
	 * Copies images (both database entries and files) to another gallery
	 * @param Array $images array of images or images ids
	 * @param int|stdClass|C_NextGen_Gallery $desintation_gallery
	 */
	function copy_images($images, $destination_gallery);


	/**
	 * Moves images (both database entries and files) to another gallery
	 * @param Array $images array of images or images ids
	 * @param int|stdClass|C_NextGen_Gallery $desintation_gallery
	 */
	function move_images($images, $destination_gallery);

	/**
	 * Returns TRUE if the specified user (defaults to current logged in user)
	 * can manage this gallery by checking the 'NextGEN Manage others gallery'
	 * capability
	 * @param int|stdClass|C_NextGen_Gallery $gallery
	 * @param int|WP_User|stdClass $user defaults to current logged in user
	 */
	function can_manage_this_gallery($gallery, $user=FALSE);


	/**
	 * Sets the preview image for the specified gallery
	 *  @param int|stdClass|C_NextGen_Gallery $gallery
	 */
	function set_gallery_preview_image($gallery);


}