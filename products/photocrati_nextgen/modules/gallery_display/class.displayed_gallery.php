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
	 * @param FALSE|C_Displayed_Gallery_Mapper $mapper
	 * @param array|stdClass|C_Displayed_Gallery $properties
	 * @param FALSE|string|array $context
	 */
	function initialize($mapper=FALSE, $properties=array(), $context=FALSE)
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
	function set_defaults()
	{
		// If the display type is set, then get it's settings and apply them as
		// defaults to the "display_settings" of the displayed gallery
		if (isset($this->object->display_type)) {

			// Get display type mapper
			$display_type = $this->object->get_display_type();
			if (!$display_type) {
				$this->object->add_error('Invalid display type', 'display_type');
			}
			else {
				$this->object->display_settings = $this->object->array_merge_assoc(
					$display_type->settings, $this->object->display_settings
				);
			}
		}
	}


	function validate()
	{
		$this->object->set_defaults();

		$this->object->validates_presence_of('source');
		$this->object->validates_presence_of('display_type');
		if (in_array($this->object->source, array('galleries', 'albums', 'tags'))) {
			$this->object->validates_presence_of('container_ids');
		}

		// Validate the display settings
		$display_type = $this->object->get_display_type();
		$display_type->settings = $this->object->display_settings;
		if (!$display_type->validate()) {
			foreach ($display_type->get_errors() as $property => $errors) {
				foreach ($errors as $error) {
					$this->object->add_error($error, $property);
				}
			}
		}

		return $this->object->is_valid();
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
	function get_images($limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		// Get the image mapper
		$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
		$image_key = $mapper->get_primary_key_column();

		// Create query
		$mapper->select($id_only ? $image_key : '*')->where(
			array("galleryid in (%s)", $this->object->container_ids)
		);
		$mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));
		if ($limit) $mapper->limit($limit, $offset);

		return $mapper->run_query();
	}


	/**
	 * Get the galleries associated with this display
	 */
	function get_galleries()
	{
		$retval = array();
		if ($this->object->source == 'gallery') {
			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');
			$gallery_key = $mapper->get_primary_key_column();
			$mapper->select()->where(array("{$gallery_key} IN (%s)", $this->object->container_ids));
			return $mapper->run_query();
		}
		return $retval;
	}


	/**
	 * Gets the number of images to display
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @return int
	 */
	function get_image_count($limit=FALSE, $offset=FALSE)
	{
		$result = $this->object->get_images($limit, $offset, TRUE);
		if ($result) $result = count($result);
		else $result = 0;
		return $result;
	}

	/**
	 * Gets the display type object used in this displayed gallery
	 * @return C_Display_Type
	 */
	function get_display_type()
	{
		$mapper = $this->object->_get_registry()->get_utility('I_Display_Type_Mapper');
		return  $mapper->find_by_name($this->object->display_type, TRUE);
	}
}