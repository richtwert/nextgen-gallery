<?php

interface I_Active_Record
{
    function id();

    function is_new();
    
    function save();
    
    function delete();
    
    function find($id, $context);
    
    function find_all($order, $start, $limit, $context);
    
    function find_by($where, $vars, $order, $start, $limit, $context);
    
    function is_valid();
    
    function get_errors();
    
    function set_properties($value);
}