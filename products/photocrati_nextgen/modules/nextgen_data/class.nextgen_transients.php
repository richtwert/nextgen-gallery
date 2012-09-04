<?php

class C_NextGen_Transients extends C_Component
{

    public static $_instances = array();
    public $_prefix = 'ngg_';

    function define()
    {
        parent::define();
        $this->implement('I_Transients');
    }

    function initialize($context = False)
    {
        parent::initialize($context);
    }

    static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_NextGen_Transients($context);
        }
        return self::$_instances[$context];
    }

    function get_value($name)
    {
        return get_transient($this->_prefix . $name);
    }

    function set_value($name, $value, $expiration = NULL)
    {

        if (is_null($expiration))
        {
            $expiration = 60 * 60 * 1;
        }

        return set_transient(
            $this->_prefix . $name,
            $value,
            $expiration
        );

    }

}
