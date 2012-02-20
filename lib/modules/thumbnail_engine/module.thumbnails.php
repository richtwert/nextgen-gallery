<?php

/***
	{
		Module: photocrati-thumbnail_engine,
                Depends: { photocrati-attach_from_post_type }
	}
***/

class M_Thumbnails extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
        		'photocrati-thumbnail_engine',
            'Thumbnails',
            'Provides a mechanism for adjusting the thumbnail configuration',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Component_Factory', 'A_Thumbnail_Factory');
        $this->_registry->add_adapter('I_Gallery_Image', 'A_Thumbnail_Paths');
        $this->_registry->add_adapter('I_Gallery_Image', 'A_Generate_Thumbnails', 'imported_image');
        $this->_registry->add_adapter('I_attached_gallery', 'A_attached_gallery_Thumbnails');
    }
}

new M_Thumbnails();
