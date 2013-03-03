<?php

class A_Image_Options_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Image Options';
	}

	/**
	 * Returns the options available for sorting images
	 * @return array
	 */
	function _get_image_sorting_options()
	{
		return array(
			'Custom'					=>	'sortorder',
			'Image ID'					=>	'pid',
			'Filename'					=>	'filename',
			'Alt/Title Text'			=>	'alttext',
			'Date/Time'					=>	'imagedate'
		);
	}


	/**
	 * Returns the options available for sorting directions
	 * @return array
	 */
	function _get_sorting_direction_options()
	{
		return array(
			'Ascending'					=>	'ASC',
			'Descending'				=>	'DESC'
		);
	}


	/**
	 * Returns the options available for matching related images
	 */
	function _get_related_image_match_options()
	{
		return array(
			'Categories'				=>	'category',
			'Tags'						=>	'tags'
		);
	}

	function render()
	{
		$settings = $this->object->get_model();
		return $this->render_partial('nextgen_other_options#image_options_tab', array(
			'gallery_path_label'			=>	_('Where would you like galleries stored?'),
			'gallery_path_help'				=>	_('Where galleries and their images are stored'),
			'gallery_path'					=>	$settings->gallerypath,
			'delete_image_files_label'		=>	_('Delete Image Files?'),
			'delete_image_files_help'		=>	_('When enabled, image files will be removed after a Gallery has been deleted'),
			'delete_image_files'			=>	$settings->deleteImg,
			'show_related_images_label'		=>	_('Show Related Images on Posts?'),
			'show_related_images_help'		=>	_('When enabled, related images will be appended to each post'),
			'show_related_images'			=>	$settings->activateTags,
			'related_images_hidden_label'	=>	_('(Show Customization Settings)'),
			'related_images_active_label'	=>	_('(Hide Customization Settings)'),
			'match_related_images_label'	=>	_('How should related images be match?'),
			'match_related_images'			=>	$settings->appendType,
			'match_related_image_options'	=>	$this->object->_get_related_image_match_options(),
			'max_related_images_label'		=>	_('Maximum # of related images to display'),
			'max_related_images'			=>	$settings->maxImages,
			'sorting_order_label'			=>	_("What's the default sorting method?"),
			'sorting_order_options'			=>	$this->object->_get_image_sorting_options(),
			'sorting_order'					=>	$settings->galSort,
			'sorting_direction_label'		=>	_('Sort in what direction?'),
			'sorting_direction_options'		=>	$this->object->_get_sorting_direction_options(),
			'sorting_direction'				=>	$settings->galSortDir,
			'automatic_resize_label'		=>	'Automatically resize images after upload',
			'automatic_resize_help'			=>	'It is recommended that your images be resized to be web friendly',
			'automatic_resize'				=>	$settings->imgAutoResize,
			'resize_images_label'			=>	_('What should images be resized to?'),
			'resize_images_help'			=>	_('After images are uploaded, they will be resized to the above dimensions and quality'),
			'resized_image_width_label'		=>	_('Width:'),
			'resized_image_height_label'	=>	_('Height:'),
			'resized_image_quality_label'	=>	_('Quality:'),
			'resized_image_width'			=>	$settings->imgWidth,
			'resized_image_height'			=>  $settings->imgHeight,
			'resized_image_quality'			=>	$settings->imgQuality,
			'backup_images_label'			=>	_('Backup the original images?'),
			'backup_images_yes_label'		=>	_('Yes'),
			'backup_images_no_label'		=>	_('No'),
			'backup_images'					=>	$settings->imgBackup
		), TRUE);
	}
}