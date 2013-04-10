<?php
/*
{
    Module: photocrati-nextgen_pagination
}
*/
class M_NextGen_Pagination extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_pagination',
            "Pagination",
            "Provides pagination for display types",
            '0.1',
            "http://www.nextgen-gallery.com",
            "Photocrati Media",
            "http://www.photocrati.com"
        );
    }
}

new M_NextGen_Pagination;