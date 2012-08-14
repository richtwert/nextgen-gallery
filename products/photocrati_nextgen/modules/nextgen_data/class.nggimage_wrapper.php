<?php

class C_nggImage_Wrapper extends C_Component
{
    function define($args, $context=FALSE)
    {
		parent::define($context);
        $this->wrap('nggImage', array(&$this, 'instantiate_wrapped_class'), $args);
    }

    function instantiate_wrapped_class($args)
    {
        $obj = new nggImage($args);
        foreach ($args as $key => $value) $obj->$key = $value;
        return $obj;
    }

    function id()
    {
    	return $this->pid;
    }
}
