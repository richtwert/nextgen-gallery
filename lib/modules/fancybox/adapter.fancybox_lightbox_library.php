<?php

class A_Fancybox_Lightbox_Library extends Mixin
{   
    function initialize()
    {
        $this->object->add_post_hook(
            '_load_libraries',
            'Add fancybox support',
            get_class(),
            'add_fancybox_support'
        );
    }
    
    function add_fancybox_support()
    {
        // Get libraries
        $libraries = $this->object->get_method_property(
            $this->method_called,
            'return_value'
        );
        
        // If fancybox is missing, then add it!
        if (!isset($libraries['fancybox'])) {
            $libraries['fancybox'] = array(
                'script'            => 'fancybox',
                'javascript_code'   => 'jQuery(function($){$(".fancybox").fancybox();});',
                'html'              => "class='fancybox' rel='%GALLERY_NAME%'"
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