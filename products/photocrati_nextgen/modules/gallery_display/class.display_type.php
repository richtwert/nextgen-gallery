<?php

/**
 * A Display Type is a component which renders a collection of images
 * in a "gallery".
 *
 * Properties:
 * - entity_type (gallery, album)
 * - name		 (nextgen_basic-thumbnails)
 * - title		 (NextGEN Basic Thumbnails)
 */
class C_Display_Type extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Display_Type_Mapper';

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Display_Type_Validation');
		$this->implement('I_Display_Type');
	}

	/**
	 * Initializes a display type with properties
	 * @param FALSE|C_Display_Type_Mapper $mapper
	 * @param array|stdClass|C_Display_Type $properties
	 * @param FALSE|string|array $context
	 */
	function initialize($mapper=FALSE, $properties=array(), $context=FALSE)
	{
		// If no mapper was specified, then get the mapper
		if (!$mapper) $mapper = $this->_get_registry()->get_utility($this->_mapper_interface);

		// Construct the model
		parent::initialize($mapper, $properties, $context);
	}
}

class Mixin_Display_Type_Validation extends Mixin
{
	function validate()
	{
		$this->object->set_defaults();

		$this->object->validates_presence_of('entity_type');
		$this->object->validates_presence_of('name');
		$this->object->validates_presence_of('title');

		return $this->object->is_valid();
	}


	function set_defaults()
	{
		if (!isset($this->object->settings)) $this->object->settings = array();
	}
}