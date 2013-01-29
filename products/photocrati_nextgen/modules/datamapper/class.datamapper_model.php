<?php

class C_DataMapper_Model extends C_Component
{
	var $_mapper;
	var $_stdObject;
	var $_errors = array();

	/**
	 * Define the model
	 */
	function define($mapper, $properties, $context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_DataMapper_Model_Instance_Methods');
		$this->add_mixin('Mixin_Validation');
		$this->add_mixin('Mixin_DataMapper_Model_Validation');
		$this->implement('I_DataMapper_Model');
	}

	/**
	 * Creates a new entity for the specified mapper
	 * @param C_DataMapper_Driver_Base $mapper
	 * @param array|stdClass $properties
	 * @param string $context
	 */
	function initialize($mapper, $properties=FALSE)
	{
		$this->_mapper = $mapper;
		$this->_stdObject = $properties ? (object)$properties  : new stdClass();
		parent::initialize();
		$this->set_defaults();
	}

	/**
	 * Gets a property of the model
	 */
	function &__get($property_name)
	{
		$entity = $this->get_entity();
		if (isset($entity->$property_name)) {
			$retval = &$entity->$property_name;
			return $retval;
		}
		else {
			// We need to assign NULL to a variable first, since only
			// variables can be returned by reference
			$retval = NULL;
			return $retval;
		}
	}

	/**
	 * Sets a property for the model
	 */
	function __set($property_name, $value)
	{
		$entity = $this->get_entity();
		return $entity->$property_name = $value;
	}


	function __isset($property_name)
	{
		return isset($this->get_entity()->$property_name);
	}
}

class Mixin_DataMapper_Model_Instance_Methods extends Mixin
{
	/**
	 * Gets/sets the primary key
	 */
	function id()
	{
		$key = $this->object->get_mapper()->get_primary_key_column();
		$args = func_get_args();
		if ($args) {
			return $this->object->__set($key, $args[0]);
		}
		else {
			return $this->object->__get($key);
		}
	}

	/**
	 * Determines whether the object is new or existing
	 * @return type
	 */
	function is_new()
	{
		return $this->object->id() ? FALSE: TRUE;
	}

	/**
	 * Destroys or deletes the entity
	 */
	function destroy()
	{
		$this->object->get_mapper()->destroy($this->object->get_entity());
	}

	/**
	 * Sets the default values for this model
	 */
	function set_defaults()
	{
		$this->object->get_mapper()->set_defaults($this);
	}


	/**
	 * Updates the attributes for an object
	 */
	function update_attributes($array=array())
	{
		$entity = $this->object->get_entity();
		foreach ($array as $key => $value) $entity->$key = $value;
		$this->object->_stdObject = $entity;
		return $this->object;
	}

	/**
	 * Saves the entity
	 * @param type $updated_attributes
	 */
	function save($updated_attributes=array())
	{
		$this->object->update_attributes($updated_attributes);
		return $this->object->get_mapper()->save($this->object->get_entity());
	}


	/**
	 * Returns the associated entity
	 */
	function &get_entity()
	{
		return $this->object->_stdObject;
	}

	/**
	 * Gets the data mapper for the entity
	 * @return C_DataMapper_Driver_Base
	 */
	function get_mapper()
	{
		return $this->object->_mapper;
	}
}

/**
 * This mixin should be overwritten by other modules
 */
class Mixin_DataMapper_Model_Validation extends Mixin
{
	function validation()
	{
		return $this->object->is_valid();
	}
}
