<?php

class C_nggImage_Wrapper extends C_Component
{   
    function define($args, $context)
    {
        $this->wrap('nggImage', array(&$this, 'instantiate_wrapped_class'), $args);
    }
    
    function initialize($args, $context=FALSE)
    {
        parent::initialize($context);
    }
    
    function instantiate_wrapped_class($args)
    {
        $obj = new nggImage($args);
        foreach ($args as $key => $value) $obj->$key = $value;
        return $obj;
    }
    
}
