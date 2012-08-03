<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Mixin_Component_Config extends Mixin
{
    function initialize()
    {
        $this->option_name = $this->object->get_option_name();
    }

	/**
	 * Returns the name of the option used to store the settings
	 * @return type
	 */
	function get_option_name()
	{
		return get_class($this->object);
	}


	/**
	 * Saves all settings
	 * @return boolean
	 */
    function save()
    {
        $this->object->validate();
        if ($this->object->is_valid())
            return update_option($this->option_name, $this->object->settings);
        else
            return FALSE;
    }


	/**
	 * Deletes all settings for the component
	 * @return type
	 */
    function delete()
    {
        return update_option($this->option_name, array());
    }


	/**
	 * Loads all settings
	 */
    function _load()
    {
        return $this->object->settings = array_merge(
            $this->object->settings,
            get_option($this->option_name, array())
        );
    }


	/**
	 * Sets any defaults missing from the loaded settings
	 */
	function _set_defaults()
	{

	}
}


class C_Base_Component_Config extends C_Component
{
    var $settings = array();

    function define()
    {
		parent::define();

        $this->implement('I_Component_Config');
        $this->add_mixin('Mixin_Component_Config');
        $this->add_mixin('Mixin_Validation');
    }

    function initialize($settings=FALSE, $context=FALSE)
    {
        if (!$settings) $settings = array();
        if ($this->has_method('_set_defaults'))  $this->_set_defaults();
        if ($this->has_method('_load'))          $this->_load();

        $this->settings = array_merge($this->settings, $settings);
        parent::initialize($context);
    }

    function __get($name)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : FALSE;
    }


    function __set($name, $value)
    {
        return $this->settings[$name] = $value;
    }


	function __isset($property)
	{
		return isset($this->settings[$property]);
	}
}
