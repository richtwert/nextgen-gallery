<?php

/**
 * WordPres Site-specific setting management
 */
class Mixin_Settings_Manager_Instance_Methods extends Mixin
{
	/**
	 * Returns the name of the WordPress option where settings are stored
	 * @return type
	 */
	function _get_option_name()
	{
		return 'module_settings';
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
			$retval  = $this->object->_options[$property];
		}
		return $retval;
	}

	/**
	 * Sets a setting to a particular value
	 * @param string $property
	 * @param mixed $value
	 * @returns C_Settings_Manager
	 */
	function set($property, $value=NULL)
	{
		if (is_array($property)) {
			foreach ($property as $key => $value) {
				$this->object->set($key, $value);
			}
		}
		else {
			$this->object->_options[$property] = $value;
		}

		return $this->object;
	}

	/**
	 * Determines whether a particular setting has been configured
	 * @param string $property
	 * @return boolean
	 */
	function is_set($property)
	{
		return isset($this->object->_options[$property]);
	}

	/**
	 * Loads the settings from the persistence layer
	 */
	function load()
	{
		$this->object->_options = get_option($this->object->_get_option_name(), array());
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

	function is_default($key)
	{
		$retval = FALSE;
		if (isset($this->object->_defaults[$key]) && $this->object->_defaults[$key] == $this->object->get($key)) {
			$retval = TRUE;
		}
		return $retval;
	}

	function set_default($key, $value)
	{
		if ((!$this->object->is_set($key)) OR $this->object->is_default($key)) $this->object->set($key, $value);
		$this->object->_defaults[$key] = $value;
		return $this->object;

	}

	function set_defaults()
	{
		foreach ($this->object->_defaults as $key=>$value) {
			if (!isset($this->object->$key)) $this->object->set($key,$value);
		}
		return $this->object;
	}

	/**
	 * Persists the settings
	 * @return boolean
	 */
	function save()
	{
		return update_option($this->object->_get_option_name(), $this->object->_options);
	}

	/**
	 * Removes settings completely
	 */
	function destroy()
	{
		delete_option($this->object->_get_option_name());
	}
}

class C_Settings_Manager extends C_Component implements ArrayAccess
{
	static $_instances		= array();
	var $_options			= array();
	var $_defaults			= array();
	var $_errors			= array();

	/**
	 * Returns the singleton instance of the settings manager
	 * @param string $context
	 * @return C_Settings_Manager
	 */
	static function &get_instance($context)
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
		$this->add_mixin('Mixin_Validation');
		$this->implement('I_Settings_Manager');
	}


	function initialize()
	{
		parent::initialize();
		$this->load();
	}
	
	function group($name)
	{
		return $this->get_registry()->get_utility('I_Settings_Manager', $name);
	}

	/**
	 * Determines whether a particular setting has been set
	 * @param string $property
	 * @return boolean
	 */
	function __isset($property)
	{
		return $this->object->is_set($property);
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
	 * @return mixed
	 */
	function __get($property)
	{
		return $this->get($property);
	}

	/**
	 * Sets the value of a setting
	 * @param string $property
	 * @param mixed $value
	 * @return C_Settings_Manager
	 */
	function __set($property, $value)
	{
		$this->set($property, $value);
		return $this;
	}

	/**
	 * Determines whether a particular setting has been set
	 * @param string $offset
	 * @return boolean
	 */
	function offsetExists($offset)
	{
		return $this->is_set($offset);
	}

	/**
	 * Sets a setting to a particular value (while being accessed as an array)
	 * @param string $offset
	 * @param mixed $value
	 * @return C_Settings_Manager
	 */
	function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
		return $this;
	}

	/**
	 * Gets the value of a particular setting (while being accessed as an array)
	 * @param string $offset
	 * @return mixed
	 */
	function offsetGet($offset)
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
