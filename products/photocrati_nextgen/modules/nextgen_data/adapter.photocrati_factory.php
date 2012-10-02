<?php

class A_Photocrati_Factory extends Mixin
{
    function gallery($mapper=FALSE, $properties=array(), $context=FALSE)
    {
        return new C_Gallery($properties, $mapper, $context);
    }


    function gallery_image($mapper=FALSE, $properties=array(), $context=FALSE)
    {
        return new C_Image($properties, $mapper, $context);
    }


    function image($mapper=FALSE, $properties=array(), $context=FALSE)
    {
        return new C_Image($properties, $mapper, $context);
    }


    function album($mapper=FALSE, $properties=array(), $context=FALSE)
    {
        return new C_Album($mapper, $properties, $context);
    }
}
