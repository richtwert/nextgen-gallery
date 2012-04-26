<?php

class C_Admin_Menu_Registry
{
    static $_menus = array();
    
    static function add($id, $menu_title, $menu_action_name)
    {
        self::$_menus[$id] = array(
            'id'                => $id,
            'menu_title'        => $menu_title,
            'menu_action_name'  => $menu_action_name
        );
    }
    
    static function remove($id)
    {
        unset(self::$_menus[$id]);
    }
    
    
    static function get($id)
    {
        return self::$_menus[$id];
    }
    
    
    static function get_all()
    {
        return self::$_menus;
    }
    
}