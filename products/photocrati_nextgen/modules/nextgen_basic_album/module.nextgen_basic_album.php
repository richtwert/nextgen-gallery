<?php

/**
{
    Module:		photocrati-nextgen_basic_album,
    Depends:	{ photocrati-gallery_display }
}
 **/

define('NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM', 'photocrati-nextgen_basic_compact_album');
define('NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM', 'photocrati-nextgen_basic_extended_album');

class M_NextGen_Basic_Album extends C_Base_Module
{
	var $module_id = 'photocrati-nextgen_basic_album';

	function define()
    {
        parent::define(
            'photocrati-nextgen_basic_album',
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
		// Add module activation
        $this->get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_NextGen_Basic_Album_Activator'
		);

		// Add validation for album display settings
        $this->get_registry()->add_adapter(
			'I_Display_Type',
			'A_NextGen_Basic_Album'
		);

		// Add a controller for displaying albums on the front-end
        $this->get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_Basic_Album_Controller',
			array(
				NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM,
				NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM,
				$this->module_id
			)
		);

		// Add a mapper for setting the defaults for the album
        $this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_Album_Mapper'
		);

		// Add a generic adapter for display types to do late url rewriting
		$this->get_registry()->add_adapter(
			'I_Displayed_Gallery_Renderer',
			'A_NextGen_Basic_Album_Routes'
		);
    }
}


new M_NextGen_Basic_Album();