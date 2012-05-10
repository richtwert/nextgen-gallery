<?php

class C_DataMapper_Model extends C_Component
{
	var $_mapper;
	var $_stdObject;

	/**
	 * Creates a new entity for the specified mapper
	 * @param C_DataMapper_Driver_Base $mapper
	 * @param array|stdClass $properties
	 * @param string $context
	 */
	function initialize($mapper, $properties=FALSE, $context = FALSE)
	{
		parent::initialize($context);
		$this->_mapper = $mapper;
		$this->_stdObject = $properties ? (object)$properties  : new stdClass();
	}

	function define()
	{
		$this->add_mixin('Mixin_Validation');
		$this->implement('I_DataMapper_Model');
	}

	/**
	 * Gets the data mapper for the entity
	 * @return C_DataMapper_Driver_Base
	 */
	function get_mapper()
	{
		return $this->_mapper;
	}

	/**
	 * Returns the associated entity
	 */
	function &get_entity()
	{
		return $this->_stdObject;
	}


	/**
	 * Gets a property of the model
	 */
	function __get($property_name)
	{
		return property_exists($this->_stdObject, $property_name) ? $this->_stdObject->$property_name : NULL;
	}

	/**
	 * Sets a property for the model
	 */
	function __set($property_name, $value)
	{
		return $this->_stdObject->$property_name = $value;
	}


	function __isset($property_name)
	{
		return isset($this->_stdObject->$property_name);
	}


	/**
	 * Saves the entity
	 * @param type $updated_attributes
	 */
	function save($updated_attributes=array())
	{
		$this->update_attributes($updated_attributes);
		return $this->get_mapper()->save($this->get_entity());
	}

	/**
	 * Updates the attributes for an object
	 */
	function update_attributes($array=array())
	{
		foreach ($array as $key => $value) $this->_stdObject->$key = $value;
	}

	/**
	 * Destroys or deletes the entity
	 */
	function destroy()
	{
		$this->get_mapper()->destroy($this->_stdObject);
	}


	/**
	 * Determines whether the object is new or existing
	 * @return type
	 */
	function is_new()
	{
		return $this->id() ? FALSE: TRUE;
	}

	/**
	 * Gets the primary key
	 */
	function id()
	{
		$key = $this->get_mapper()->get_primary_key_column();
		return $this->__get($key);
	}
}

?>
