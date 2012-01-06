<?php

class A_Thumbnail_Factory extends Mixin
{
    function thumbnail_config($settings=array(), $context=FALSE)
    {
        return new C_Thumbnail_Config($settings, $context);
    }
    
    function thumbnail_generator($image, $config, $context=FALSE)
    {
        return new C_Thumbnail_Generator($image, $config, $context);
    }
}