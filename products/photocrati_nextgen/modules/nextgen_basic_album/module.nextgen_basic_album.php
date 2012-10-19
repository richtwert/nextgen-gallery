<?php

/**
{
    Module:		photocrati-nextgen_basic_album,
    Depends:	{ photocrati-gallery_display }
}
 **/

define('PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM', 'photocrati-nextgen_basic_album');

class M_NextGen_Basic_Album extends C_Base_Module
{
    function define()
    {
        parent::define(
            PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM,
            'NextGEN Basic Album',
            "Provides support for NextGEN's Basic Album",
            '0.1',
            'http://nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }


    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Display_Type',            'A_NextGen_Basic_Album');
        $this->get_registry()->add_adapter('I_NextGen_Activator',       'A_NextGen_Basic_Album_Activator');
        $this->get_registry()->add_adapter('I_Display_Type_Controller', 'A_NextGen_Basic_Album_Controller');
        $this->get_registry()->add_adapter('I_Display_Type_Mapper',     'A_NextGen_Basic_Album_Mapper');
    }
}


new M_NextGen_Basic_Album();