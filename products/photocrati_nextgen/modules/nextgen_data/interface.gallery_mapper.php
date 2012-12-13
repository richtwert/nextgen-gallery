<?php

interface I_Gallery_Mapper
{
	/**
	 * Sets the preview image for the specified gallery
	 *  @param int|stdClass|C_Gallery $gallery
	 */
	function set_gallery_preview_image($gallery);

    static function get_instance($context = False);
}
