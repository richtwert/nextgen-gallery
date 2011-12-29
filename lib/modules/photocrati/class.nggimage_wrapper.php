<?php

class C_nggImage_Wrapper extends C_Component
{
    function define($args)
    {
        $this->wrap('nggImage', array(&$this, 'instantiate_wrapped_class'), $args);
    }
    
    function instantiate_wrapped_class($args)
    {
        return new nggImage($args);
    }
}