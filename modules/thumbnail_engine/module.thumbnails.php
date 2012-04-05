<?php

/***
	{
		Module: photocrati-thumbnail_engine
	}
***/

class M_Thumbnails extends C_Base_Module
{
    function define()
    {
        parent::define(
        		'photocrati-thumbnail_engine',
            'Thumbnails',
            'Provides a mechanism for adjusting the thumbnail configuration',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function initialize()
    {
    }
    
    
    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Thumbnail_Factory');
        $this->_get_registry()->add_adapter('I_Gallery_Image', 'A_Thumbnail_Paths');
        $this->_get_registry()->add_adapter('I_Gallery_Image', 'A_Generate_Thumbnails', 'imported_image');
        $this->_get_registry()->add_adapter('I_Attached_Gallery', 'A_Attached_Gallery_Thumbnails');
    }
}

new M_Thumbnails();
