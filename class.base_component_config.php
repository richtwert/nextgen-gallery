<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Mixin_Component_Config extends Mixin
{
    function initialize()
    {
        $this->option_name = get_class($this->object);
    }


    function save()
    {
        $this->object->validate();
        if ($this->object->is_valid())
            return update_option($this->option_name, $this->object->settings);
        else
            return FALSE;
    }


    function delete()
    {
        return update_option($this->option_name, array());
    }


    function _load()
    {
        return $this->object->settings = array_merge(
            $this->object->settings,
            get_option($this->option_name, array())
        );
    }

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
//        $this->add_mixin('Mixin_Active_Record_Validation');
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
