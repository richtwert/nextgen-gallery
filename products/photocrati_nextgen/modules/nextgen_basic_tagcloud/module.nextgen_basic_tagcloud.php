<?php

/***
{
        Module:     photocrati-nextgen_basic_tagcloud,
        Depends:    { photocrati-gallery_display }
}
 ***/

define('NEXTGEN_BASIC_TAG_CLOUD_MODULE_NAME', 'photocrati-nextgen_basic_tagcloud');

class M_NextGen_Basic_Tagcloud extends C_Base_Module
{
    function define()
    {
        parent::define(
			NEXTGEN_BASIC_TAG_CLOUD_MODULE_NAME,
            'NextGen Basic Tagcloud',
            'Provides a tagcloud for NextGEN Gallery',
            '1.9.6',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }


    function _register_adapters()
    {
        // Installs the display type
        $this->get_registry()->add_adapter(
            'I_NextGen_Activator',
            'A_NextGen_Basic_Tagcloud_Activation'
        );

        // Provides settings fields and frontend rendering
        $this->get_registry()->add_adapter(
            'I_Display_Type_Controller',
            'A_NextGen_Basic_Tagcloud_Controller',
            $this->module_id
        );

        // Provides validation for the display type
        $this->get_registry()->add_adapter(
            'I_Display_Type',
            'A_NextGen_Basic_Tagcloud'
        );

		// Provides default values for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_TagCloud_Mapper'
		);

		// Add routing for ngglegacy routes
		$this->get_registry()->add_adapter(
			'I_Routing_App',
			'A_NextGen_Basic_TagCloud_Routes'
		);

		// Add legacy urls
		$this->get_registry()->add_adapter(
			'I_Routing_App',
			'A_NextGen_Basic_TagCloud_Urls'
		);
    }
}

new M_NextGen_Basic_Tagcloud();
