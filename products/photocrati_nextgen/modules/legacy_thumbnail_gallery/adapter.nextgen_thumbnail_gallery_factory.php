<?php

class A_NextGen_Thumbnail_Gallery_Factory extends Mixin
{
    function nextgen_thumbnail_gallery_config($settings, $context=FALSE)
    {
        return new C_NextGen_Thumbnail_Gallery_Config($settings, $context);
    }
}