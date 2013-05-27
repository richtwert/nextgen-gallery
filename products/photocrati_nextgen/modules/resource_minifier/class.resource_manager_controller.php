<?php

class C_Resource_Manager_Controller extends C_MVC_Controller
{
    static $_instances = array();
    static function get_instance($context)
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
        $this->implement('I_Resource_Manager');
    }

    function static_scripts_action()
    {
        // TODO: All wp_register_script calls need to use relative urls
        // TODO: Use file_get_contents() when $handle doesn't have 'http(s)' prefix
        // TODO: We should probably still check for site_url()
        echo('alert("Static scripts loaded!");');
    }

    function dynamic_scripts_action()
    {
        echo('alert("Dynamic scripts loaded!");');
    }

    function static_styles_action()
    {
        echo "/** In static styles **/";
    }

    function dynamic_styles_action()
    {
        echo "/** In dynamic styles **/";
    }
}