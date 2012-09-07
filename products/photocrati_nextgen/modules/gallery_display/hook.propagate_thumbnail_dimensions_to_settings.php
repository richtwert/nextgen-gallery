<?php

/**
 * Propagates saving thumbnail dimensions to global NextGEN Settings
 */
class Hook_Propagate_Thumbnail_Dimensions_To_Settings extends Hook
{
	function save($entity)
	{
		if ($this->object->get_method_property('save',
		  ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE)) {
			$settings		= $this->object->get_registry()->get_utility('I_NextGen_Settings');
			$width			= $entity->settings['thumbnail_width'];
			$height			= $entity->settings['thumbnail_height'];
			$new_dimension	= "{$width}x{$height}";
			$dimensions		= $settings->thumbnail_dimensions;

			if (!in_array($new_dimension, $dimensions)) {
				$dimensions[]	= $new_dimension;
				sort($dimensions);
				$settings->thumbnail_dimensions = $dimensions;
				$settings->save();
			}

		}
	}
}