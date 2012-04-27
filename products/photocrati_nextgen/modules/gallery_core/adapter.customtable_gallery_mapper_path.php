<?php

class A_CustomTable_Gallery_Mapper_Path extends Mixin
{
	/**
	 * Returns the upload path for a gallery
	 * @return string
	*/
	function get_gallery_path($gallery_id)
	{
		$gallery = $this->object->find($gallery_id);
		return $gallery->path;
	}


	/**
	 * If the gallery was saved successfully, then we add the path to
	 * the gallery and save again
	 * @param stdObject|stdClass $entity
	 * @return boolean
	 */
	function _save_entity(&$entity)
	{
		$retval = FALSE;

		if ($this->call_parent()) {
			$storage = $this->_get_registry()->get_utility('I_Gallery_Storage');
			$key = $this->object->get_primary_key_column();
			$entity->path = path_join($storage->get_upload_path(), $entity->$key);
			$retval = $this->call_parent();
		}

		return $retval;
	}
}