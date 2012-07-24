<?php

interface I_Gallery_Mapper
{
	/**
	 * Sets the preview image for the specified gallery
	 *  @param int|stdClass|C_NextGen_Gallery $gallery
	 */
	function set_gallery_preview_image($gallery);
}