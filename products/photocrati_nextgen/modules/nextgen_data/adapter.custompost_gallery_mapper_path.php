<?php


class A_CustomPost_Gallery_Mapper_Path extends Mixin
{
	/**
	 * Returns the upload path for a gallery
	 * @return string
	*/
	function get_gallery_path($gallery_id)
	{
		$storage = $this->object->_get_registry()->get_utility('I_Gallery_Storage');
		return $storage->get_upload_path();
	}
}