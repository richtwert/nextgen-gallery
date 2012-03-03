<?php

class A_NextGen_ImageBrowser_Resources extends Mixin
{
    function nextgen_imagebrowser_css()
    {
        readfile(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
            'css', 'nggallery.css'
        ))));
    }    
}


