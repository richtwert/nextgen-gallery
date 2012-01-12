<?php

class A_Thickbox_Lightbox_Library extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            '_load_libraries',
            'Add thickbox support',
            get_class(),
            'add_thickbox_support'
        );
    }
    
    
    function add_thickbox_support()
    {
        // Get libraries
        $libraries = $this->object->get_method_property(
            $this->method_called,
            'return_value'
        );
        
        // If thickbox support is missing, then add it!
        if (!isset($libraries['thickbox'])) {
            $libraries['thickbox'] = array(
                'script'            => 'thickbox',
                'style'             => 'thickbox',
                'javascript_code'   => '',
                'html'              => "class='thickbox' rel='%GALLERY_NAME%'"
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