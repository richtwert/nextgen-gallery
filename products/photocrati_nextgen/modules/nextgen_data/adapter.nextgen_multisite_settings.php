<?php

/**
 * Adjusts the C_NextGen_Settings class to manage multisite options
 */
class A_NextGen_Multisite_Settings extends Mixin
{
    function initialize()
    {
        // This handles WordPress substitutions like the %BLOG_ID placeholder
        $this->object->add_post_hook(
            'set',
            'WordPress Multisite Overrides',
            'Hook_NextGen_Settings_WordPress_MU_Overrides',
            '_apply_multisite_overrides'
        );
    }

    /**
     * Resets NextGEN to it's default settings
     *
     * @param bool $save Whether to immediately call save() when done
     * @return null
     */
    function reset($save = False)
    {
        $this->object->_global_options = array();
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

/**
 *  Hook triggered after a global option has been set()
 */
class Hook_NextGen_Settings_WordPress_MU_Overrides extends Hook
{
    function _apply_multisite_overrides($option_name, $value)
    {
        if (!$this->object->is_multisite())
        {
            return Null;
        }

        switch ($option_name) {
            case 'gallerypath':
                $blog_id = get_current_blog_id();
                $this->call_anchor(
                    $option_name,
                    str_replace('%BLOG_ID%', $blog_id, $value)
                );
                break;
        }

        return $this->object->get_method_property(
            $this->method_called,
            ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
        );
    }
}
