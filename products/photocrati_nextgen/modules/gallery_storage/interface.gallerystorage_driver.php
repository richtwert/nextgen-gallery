<?php

interface I_GalleryStorage_Driver
{
	function get_image_path($image, $size);
	function get_image_url($image, $size);
	function get_image_sizes($image);
	function get_original_path($image);
	function get_original_url($image);
	function get_thumbnail_path($image);
	function get_thumbnail_url($image);
	function upload_image($gallery_id);
}