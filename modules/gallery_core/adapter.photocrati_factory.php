<?php

class A_Photocrati_Factory extends Mixin
{   
    function gallery($properties=array(), $context=FALSE)
    {
        return new C_NextGen_Gallery($properties, $context);
    }
    
    
    function gallery_image($properties=array(), $context=FALSE)
    {
        return new C_NextGen_Gallery_Image($properties, $context);
    }
    
    
    function photocrati_options($properties=array(), $context=FALSE)
    {
        return new C_Photocrati_Internal_Options($properties, $context);
    }
    
    
    function gallery_type_controller($gallery_type, $admin=FALSE, $context=FALSE)
    {
        $retval = NULL;
        $gallery_type = C_Gallery_Type_Registry::get($gallery_type);
        if ($gallery_type) {
            if ($admin)
                $retval = new $gallery_type['admin_controller']($context);
            else
                $retval = new $gallery_type['public_controller']($context);
        }
        
        return $retval;
    }
    
    
    function gallery_type_config($gallery_type, $settings=array(), $context=FALSE)
    {
        $retval = NULL;
        
        $gallery_type = C_Gallery_Type_Registry::get($gallery_type);
        if ($gallery_type) {
            $retval = new $gallery_type['config']($settings, $context);
        }
        return $retval;
    }
    
    
    function lightbox_library($properties=array(), $context=FALSE)
    {
        return new C_Lightbox_Library($properties, $context);
    }
    
    
    function nggImage($gallery, $context=FALSE)
    {
        return new C_nggImage_Wrapper($gallery, $context);
    }
}