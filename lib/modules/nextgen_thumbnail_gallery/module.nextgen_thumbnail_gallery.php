<?php

class M_NextGen_Thumbnail_Gallery extends C_Base_Module
{
    function __construct()
    {
        parent::__construct(
            'NextGen Thumbnail Gallery',
            "Provides support for NextGen's legacy thumbnail galleries",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
        
        C_Gallery_Type_Registry::add(
            $this->module_name,
            $this->module_description,
            'C_NextGen_Thumbnail_Gallery_Settings',
            'C_NextGen_Thumbnail_Gallery_View'
        );
    }
}

new M_NextGen_Thumbnail_Gallery();