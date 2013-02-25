<?php

/**
 * WordPres Site-specific setting management
 */
class Mixin_Settings_Manager_Instance_Methods extends Mixin
{
	function _to_named_constant($property)
	{
		return $this->object->_constant_prefix.(preg_replace("/[^\w]/", "_", strtoupper($property)));
	}


	/**
	 * Returns the name of the WordPress option where settings are stored
	 * @return type
	 */
	function _get_option_name()
	{
		return $this->object->_option_name;
	}


	/**
	 * Gets the value of a particular setting
	 * @param string $property
	 * @param mixed $default
	 * @return mixed
	 */
	function get($property, $default=NULL)
	{
		$retval = $default;
		if ($this->object->is_set($property)) {
			$constant = $this->object->_to_named_constant($property);
			$retval  = defined($constant) ? eval("return {$constant};") : $this->object->_options[$property];
		}
		return $retval;
	}

	/**
	 * Sets a setting to a particular value
	 * @param string $property
	 * @param mixed $value
	 */
	function set($property, $value)
	{
		$this->object->_options[$property] = $value;
		return $this->object->get($property);
	}

	/**
	 * Determines whether a particular setting has been configured
	 * @param string $property
	 * @return boolean
	 */
	function is_set($property)
	{

		return $this->object->__isset($property);
	}

	/**
	 * Loads the settings from the persistence layer
	 */
	function load()
	{
		$this->object->options = get_option($this->object->_get_option_name(), array());
		$this->object->set_defaults();
	}

	/**
	 * Resets settings back to their defaults
	 * @param type $save
	 */
	function reset($save=FALSE)
	{
		$this->object->__options = array();
		$this->object->set_defaults();
		if ($save) $this->object->save();
	}

	/**
	 * Persists the settings
	 * @return boolean
	 */
	function save()
	{
		return update_option($this->object->_get_option_name(), $this->object->_options);
	}
}

class C_Settings_Manager extends C_Component implements ArrayAccess
{
	static $_instances		= array();
	var $_options			= array();
	var $_option_name		= 'module_settings';
	var $_constant_prefix	= 'POPE_MOD_';

	/**
	 * Returns the singleton instance of the settings manager
	 * @param string $context
	 * @return C_Settings_Manager
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Settings_Manager_Instance_Methods');
		$this->implement('I_Settings_Manager');
	}


	function initialize()
	{
		$this->load();
	}

	/**
	 * Determines whether a particular setting has been set
	 * @param string $property
	 * @return boolean
	 */
	function __isset($property)
	{
		$constant = $this->object->_to_named_constant($property);
		return defined($constant) OR isset($this->_options[$property]);
	}

	/**
	 * Unsets a configured settings
	 * @param string $property
	 * @return boolean
	 */
	function un_set($property)
	{
		unset($this->_options[$property]);
		return !$this->is_set($property);
	}

	/**
	 * Returns the value of a particular setting
	 * @param string $property
	 * @param mixed $default
	 * @return mixed
	 */
	function __get($property, $default=NULL)
	{
		return $this->get($property, $default);
	}

	/**
	 * Sets the value of a setting
	 * @param string $property
	 * @param mixed $value
	 * @return mixed
	 */
	function __set($property, $value)
	{
		return $this->set($property, $value);
	}

	/**
	 * Determines whether a particular setting has been set
	 * @param string $offset
	 * @return boolean
	 */
	function offsetExists(string $offset)
	{
		return $this->is_set($offset);
	}

	/**
	 * Sets a setting to a particular value (while being accessed as an array)
	 * @param string $offset
	 * @param mixed $value
	 * @return mixed
	 */
	function offsetSet(string $offset, $value)
	{
		return $this->set($offset, $value);
	}

	/**
	 * Gets the value of a particular setting (while being accessed as an array)
	 * @param string $offset
	 * @return mixed
	 */
	function offsetGet(string $offset)
	{
		return $this->get($offset);
	}

	/**
	 * Unconfigures a particular setting
	 * @param string $offset
	 * @return boolean
	 */
	function offsetUnset($offset)
	{
		return $this->un_set($property);
	}
}