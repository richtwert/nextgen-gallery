<?php

interface I_Router
{
    function add_route($name, $controller, $pattern);
    
    function remove_route($name);
    
    function route();
    
    static function get_instance(); 
}