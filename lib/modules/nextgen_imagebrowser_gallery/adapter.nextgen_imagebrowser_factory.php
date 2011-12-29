<?php

class A_NextGen_ImageBrowser_Factory extends Mixin
{
    function nextgen_imagebrowser_config($settings=array(), $context=FALSE)
    {
        return new C_NextGen_ImageBrowser_Config($settings, $context);
    }
}