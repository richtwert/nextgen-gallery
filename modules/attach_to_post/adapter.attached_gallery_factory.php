<?php

class A_Attached_Gallery_Factory extends Mixin
{
    function attached_gallery($properties=array(), $context=FALSE)
    {
        return new C_Attached_Gallery($properties, $context);
    }
    
    
    function attached_gallery_image($properties=array(), $context=FALSE)
    {
        return new C_Attached_Gallery_Image($properties, $context);
    }
}