<?php

interface I_GalleryStorage_Driver
{
	function get_image_sizes();
	function get_image_abspath($image, $size=FALSE);
	function get_full_abspath($image);
	function get_original_abspath($image);
	function get_thumbnail_abspath($image);
	function get_thumbs_abspath($image);
	function get_upload_abspath($gallery=FALSE);
	function get_gallery_abspath($gallery);
	function get_gallery_thumbnail_abspath($gallery);
	function get_backup_abspath($image);
	function get_image_url($image, $size=FALSE);
	function get_original_url($image);
	function get_full_url($image);
	function get_thumbnail_url($image);
	function get_thumbs_url($image);
	function get_image_html($image, $size=FALSE);
	function get_original_html($image);
	function get_full_html($image);
	function get_thumbnail_html($image);
	function get_thumbs_html($image);
	function get_image_dimensions($image, $size=FALSE);
	function get_original_dimensions($image);
	function get_full_dimensions($image);
	function get_thumbnail_dimensions($image);
	function get_thumb_dimensions($image);
	function create_thumbnail($image);
	function backup_image($image);
	function move_images($images, $gallery);
	function copy_image($images, $gallery);
	function upload_image($gallery_id, $data=FALSE, $not_a_file=FALSE);
}