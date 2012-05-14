<?php

interface I_Gallery_Mapper
{
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