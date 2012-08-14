<?php

/***
{
		Module: photocrati-nextgen-data,
		Depends: { photocrati-nextgen_settings, photocrati-datamapper }
}
***/
class Mixin_Dequeue_NextGen_Legacy_Scripts extends Mixin
{
    /**
     * Removes any queued scripts that the original NextGen legacy plugin
     * provides, as each gallery type should queue using Resource Loader
     * @global type $wp_scripts
     */
    function dequeue_scripts()
    {
        if (!is_admin()) {
			wp_dequeue_script('ngg_slideshow');
            //wp_dequeue_script('jquery-cycle');
        }
    }
}

class M_NextGen_Data extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen-data',
            'NextGEN Data Tier',
            "Provides a data tier for NextGEN gallery based on the DataMapper module",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );

        $this->add_mixin('Mixin_Dequeue_NextGen_Legacy_Scripts');
    }


    function _register_hooks()
    {
        add_action('wp_print_scripts',  array(&$this, 'dequeue_scripts'));
    }


    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Photocrati_Factory');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Parse_Image_Metadata', 'imported_image');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Auto_Rotate_Image', 'imported_image');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Auto_Resize_Image', 'imported_image');
		$this->_get_registry()->add_adapter('I_CustomPost_DataMapper', 'A_Attachment_DataMapper', 'attachment');
		$this->_get_registry()->add_adapter('I_Component_Factory', 'A_GalleryStorage_Factory');
		$this->_get_registry()->add_utility('I_Gallery_Storage', 'C_Gallery_Storage');
    }


    function _register_utilities()
    {
		$this->_get_registry()->add_utility('I_Gallery_Mapper', 'C_Gallery_Mapper');
		$this->_get_registry()->add_utility('I_Gallery_Image_Mapper', 'C_Gallery_Image_Mapper');
    }
}
new M_NextGen_Data();
