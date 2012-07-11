<?php

interface I_Thumbnail_GalleryStorage_Driver
{
	function get_gallery_thumbnail_abspath($gallery);
	function get_thumbnail_abspath($image);
	function get_thumbs_abspath($image);
	function get_thumbnail_url($image);
	function get_thumbs_url($image);
	function get_thumbnail_html($image);
	function get_thumbs_html($image);
	function get_thumbnail_dimensions($image);
	function get_thumb_dimensions($image);
	function get_image_dimensions($image, $size=FALSE);
}