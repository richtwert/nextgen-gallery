<?php

class A_Controller_Resources extends Mixin
{
    function initialize()
    {
        if (!isset($this->object->resource_loader)) {
            $this->object->resource_loader = $this->object->_get_registry()->get_singleton_utility('I_Resource_Loader');
        }
        
        if ($this->object->has_method('enqueue_scripts')) {
            $this->object->enqueue_scripts();
        }
        
        if ($this->object->has_method('enqueue_stylesheets')) {
            $this->object->enqueue_stylesheets();
        }
    }
}