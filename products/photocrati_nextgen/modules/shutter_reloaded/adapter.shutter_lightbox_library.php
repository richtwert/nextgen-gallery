<?php

class A_Shutter_Lightbox_Library extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            '_load_libraries',
            'Add Shutter Reloaded Support',
            get_class(),
            'add_shutter_support'
        );
    }
    
    function add_shutter_support()
    {
        // Get libraries
        $libraries = $this->object->get_method_property(
            $this->method_called,
            'return_value'
        );
        
        // If shutter is missing, then add it!
        if (!isset($libraries['shutter'])) {
            $libraries['shutter'] = array(
                'script'            =>  'shutter',
                'style'             =>  'shutter',
                'javascript_code'   =>  "jQuery(function($){ var shutterLinks = {}, shutterSets = {}; shutterReloaded.Init(); });",
                'html'              =>  "class='shutterset_%GALLERY_NAME%'"
            );
            
            $this->object->set_method_property(
                $this->method_called,
                'return_value',
                $libraries
            );
        }
        
        return $libraries;
    }
}