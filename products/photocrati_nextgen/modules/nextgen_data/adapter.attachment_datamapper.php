<?php

/**
 * Modifies a custom post datamapper to use the WordPress built-in 'attachment'
 * custom post type, as used by the Media Library
 */
class A_Attachment_DataMapper extends Mixin
{
	function initialize()
	{
		$this->object->_object_name = 'attachment';
	}

		/**
	 * Saves the entity using the wp_insert_attachment function
	 * instead of the wp_insert_post
	 * @param stdObject $entity
	 */
	function _save_entity($entity)
	{
		$post_id = FALSE;
		$post = $this->object->_convert_entity_to_post($entity);
		$primary_key = $this->object->get_primary_key_column();
		$filename = property_exists($entity, 'filename') ? $entity->filename : FALSE;

		if (($post_id = $attachment_id = wp_insert_attachment($entity, $filename))) {
			$new_entity = $this->object->find($post_id);
			foreach ($new_entity as $key => $value) $entity->$key = $value;

			// Merge meta data with WordPress Attachment Meta Data
			if (property_exists($entity, 'meta_data')) {
				$meta_data = wp_get_attachment_metadata($attachment_id);
				if (isset($meta_data['image_meta'])) {
					$entity->meta_data = array_merge_recursive(
						$meta_data['image_meta'],
						$entity->meta_data
					);
					wp_update_attachment_metadata($attachment_id, $entity->meta_data);
				}
			}

			// Save properties are post meta as well
			$this->object->_flush_and_update_postmeta($attachment_id, $entity, array(
					'_wp_attached_file',
					'_wp_attachment_metadata'
			));
		}

		return $attachment_id;
	}
}