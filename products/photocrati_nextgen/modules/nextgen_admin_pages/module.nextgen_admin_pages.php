<?php
/**
{
    Module: photocrati-nextgen_admin_pages
}
**/

define('NEXTGEN_ADD_GALLERY_SLUG', 'ngg_addgallery');

class M_NextGen_Admin_Pages extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_admin_pages',
            'NextGEN Admin Pages',
            'Provides admin pages for NextGEN Gallery',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Page_Manager', 'A_NextGen_Admin_Pages');
    }
}
new M_NextGen_Admin_Pages();