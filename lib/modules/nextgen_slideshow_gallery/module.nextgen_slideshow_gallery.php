<?php

class M_NextGen_Slideshow_Gallery extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
        		'photocrati-gallery-nextgen-slideshow',
            'NextGen Basic Slideshow',
            'JQuery Cycle and JW Image Rotator-based Slideshow',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
        
//        C_Gallery_Type_Registry::add(
//            $this->module_name,
//            $this->module_description,
//            'C_NextGen_Slideshow_Gallery_Settings',
//            'C_NextGen_Slideshow_Gallery_View'
//        );
    }
}

new M_NextGen_Slideshow_Gallery();
