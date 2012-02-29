<?php

class A_NextGen_Thumbnail_Gallery_Resources extends Mixin
{
    function nextgen_thumbnail_gallery_js()
    {   
        readfile(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
            'js', 'jquery.cycle.all.js'
        ))));
        
        readfile($this->find_static_file('piclens_optimized.js'));
        
        readfile(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
            'js', 'ngg.js'
        ))));
        
        readfile(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array(
            'js', 'ngg.slideshow.js'
        ))));
    }
}