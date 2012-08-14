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


    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Thumbnail_Factory');
		$this->_get_registry()->add_adapter('I_Gallery_Storage', 'A_Thumbnail_Gallery_Storage');
        $this->_get_registry()->add_adapter('I_Gallery_Image', 'A_Thumbnail_Paths');
        $this->_get_registry()->add_adapter('I_Gallery_Image', 'A_Generate_Thumbnails', 'imported_image');
        $this->_get_registry()->add_adapter('I_Attached_Gallery', 'A_Attached_Gallery_Thumbnails');
    }


	function _register_hooks()
	{
		$options = $this->_get_registry()->get_utility('I_NextGen_Settings');
		set_post_thumbnail_size(
			'thumbnail',
			$options->thumbwidth,
			$options->thumbheight,
			$options->thumbfix
		);
	}
}

new M_Thumbnails();
