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

	function define($mapper, $properties, $context=FALSE)
	{
		parent::define($mapper, $properties, $context);
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
		if (!$mapper) $mapper = $this->get_registry()->get_utility($this->_mapper_interface);

		// Construct the model
		parent::initialize($mapper, $properties, $context);
	}


	/**
	 * Allows a setting to be retrieved directly, rather than through the
	 * settings property
	 * @param string $property
	 * @return mixed
	 */
	function &__get($property)
	{
		if (isset($this->object->settings) && isset($this->object->settings[$property])) {
			$retval = &$this->object->settings[$property];
			return $retval;
		}
		else return parent::__get($property);
	}
}

class Mixin_Display_Type_Validation extends Mixin
{
	function validation()
	{
		if (!isset($this->object->defaults_set)) $this->object->set_defaults();
		$this->object->validates_presence_of('entity_type');
		$this->object->validates_presence_of('name');
		$this->object->validates_presence_of('title');

		return $this->object->is_valid();
	}


	function set_defaults()
	{
		if (!isset($this->object->settings)) $this->object->settings = array();
		if (!isset($this->object->settings['show_alternative_view_link']))
			$this->object->settings['show_alternative_view_link'] = FALSE;
		if (!isset($this->object->settings['show_return_link']))
			$this->object->settings['show_return_link'] = TRUE;
		if (!isset($this->object->settings['alternative_view_link_text']))
			$this->object->settings['alternative_view_link_text'] = '';
		if (!isset($this->object->settings['return_link_text']))
			$this->object->settings['return_link_text'] = '';
		if (!isset($this->object->preview_image_relpath))
			$this->object->preview_image_relpath = '';

		$this->object->defaults_set = TRUE;
	}
}