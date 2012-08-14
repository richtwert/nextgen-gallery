<?php

class A_Attached_Gallery_Factory extends Mixin
{
    function attached_gallery($mapper=FALSE, $properties=array(), $context=FALSE)
    {
        return new C_Attached_Gallery($properties, $mapper, $context);
    }
}