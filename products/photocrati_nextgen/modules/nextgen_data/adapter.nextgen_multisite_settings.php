<?php

/**
 * Adjusts the C_NextGen_Settings class to manage multisite options
 */
class A_NextGen_Multisite_Settings extends Mixin
{
    /**
     * Resets NextGEN to it's default settings
     *
     * @param bool $save Whether to immediately call save() when done
     * @return null
     */
    function reset($save = False)
    {
        foreach (C_NextGen_Settings_Defaults::get_defaults(True) as $name => $val)
        {
            $this->object->set($name, $val);
        }
        if ($save)
        {
            $this->object->save();
        }
    }

    /**
     * Gets the value of a setting
     *
     * @param string $option_name
     * @return mixed
     */
    function get($option_name)
    {
        $retval = Null;

        if (isset($this->object->_global_options[$option_name])) {
            $retval = $this->object->_global_options[$option_name];
        }

        return $retval;
    }

    /**
     * Sets a settings option to a particular value
     *
     * @param string $option_name
     * @param mixed $value
     * @return mixed $value
     */
    function set($option_name, $value)
    {
        $this->object->_global_options[$option_name] = $value;
        return $value;
    }

    /**
     * Removes a setting from the settings list
     *
     * @param string $option_name
     * @return null
     */
    function del($option_name)
    {
        unset($this->object->_global_options[$option_name]);
    }

    /**
     * Returns whether a setting exists
     *
     * @param string $option_name
     * @return bool isset()
     */
    function is_set($option_name)
    {
        return isset($this->object->_global_options[$option_name]);
    }

    /**
     * Returns the current options as an array
     *
     * @return array
     */
    function to_array()
    {
        return $this->object->_global_options;
    }
}