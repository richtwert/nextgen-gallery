<?php

/**
 * Associates a Display Type with a collection of images
 *
 * * Properties:
 * - source				(gallery, album, recent_images, random_images, etc)
 * - container_ids		(gallery ids, album ids, tag ids, etc)
 * - display_type		(name of the display type being used)
 * - display_settings	(settings for the display type)
 * - exclusions			(excluded entity ids)
 */
class C_Displayed_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Displayed_Gallery_Mapper';

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Displayed_Gallery_Validation');
		$this->add_mixin('Mixin_Displayed_Gallery_Instance_Methods');
		$this->implement('I_Displayed_Gallery');
	}


	/**
	 * Initializes a display type with properties
	 * @param array|stdClass|C_Displayed_Gallery $properties
	 * @param FALSE|C_Displayed_Gallery_Mapper $mapper
	 * @param FALSE|string|array $context
	 */
	function initialize($properties=array(), $mapper=FALSE, $context=FALSE)
	{
		if (!$mapper) $mapper = $this->_get_registry()->get_utility($this->_mapper_interface);

		parent::initialize($mapper, $properties, $context);
	}
}

/**
 * Provides validation
 */
class Mixin_Displayed_Gallery_Validation extends Mixin
{
	function validate()
	{
		$this->object->validates_presence_of('source');
		$this->object->validates_presence_of('display_type');
		if (in_array($this->object->source, array('galleries', 'albums', 'tags'))) {
			$this->object->validates_presence_of('container_ids');
		}

		// Get display type mapper
		$mapper = $this->object->_get_registry()->get_utility('I_Display_Type_Mapper');
		$display_type = $mapper->find_by_name($this->object->display_type);
		if (!$display_type) {
			$this->object->add_error('Invalid display type', 'display_type');
		}

		// Override the global settings with the displayed gallery's settings
		else {
			$display_type->settings = $this->object->array_merge_assoc(
				$display_type->settings, $displayed_gallery->display_settings
			);
			if ($display_type->is_invalid()) {
				foreach ($display_type->get_errors() as $property => $errors) {
					foreach ($errors as $error) {
						$displayed_gallery->add_error($error, $property);
					}
				}
			}
		}
	}
}

/**
 * Provides instance methods useful for working with the C_Displayed_Gallery
 * model
 */
class Mixin_Displayed_Gallery_Instance_Methods extends Mixin
{
	/**
	 * Gets the images associated with the displayed gallery
	 * @param int $limit
	 * @param int $offset
	 */
	function get_images($limit=FALSE, $offset=FALSE)
	{
		// Get the image mapper
		$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
		$image_key = $mapper->get_primary_key_column();

		// Create query
		$mapper->select()->where(array("galleryid in (%s)", $this->object->container_ids));
		$mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));
		if ($limit) $mapper->limit($limit, $offset);

		return $mapper->run_query();
	}
}