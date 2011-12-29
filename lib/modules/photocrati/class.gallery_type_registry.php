<?php

class C_Gallery_Type_Registry
{
    static $_gallery_types = array();
    
    static function add($name, $description, $admin_controller, $public_controller)
    {
        self::$_gallery_types[$name] = array(
          'name'                => $name,
          'description'         => $description,
          'admin_controller'    => $admin_controller,
          'public_controller'   => $public_controller
        );
    }
    
    static function remove($name)
    {
        unset(self::$_gallery_types[$name]);
    }
    
    
    static function get_all()
    {
        return self::$_gallery_types;
    }
    
    static function get($name)
    {   
        if (isset(self::$_gallery_types[$name]))
            return self::$_gallery_types[$name];
        else {
            return NULL;
        }
    }
}