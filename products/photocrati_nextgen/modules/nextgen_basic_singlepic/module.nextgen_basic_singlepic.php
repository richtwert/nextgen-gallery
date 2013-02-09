<?php

/***
{
        Module:     photocrati-nextgen_basic_singlepic,
        Depends:    { photocrati-gallery_display }
}
 ***/

define('NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME', 'photocrati-nextgen_basic_singlepic');

class M_NextGen_Basic_Singlepic extends C_Base_Module
{
    function define()
    {
        parent::define(
            NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME,
            'NextGen Basic Singlepic',
            'Provides a singlepic gallery for NextGEN Gallery',
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
            'A_NextGen_Basic_Singlepic_Activation'
        );

        // Provides settings fields and frontend rendering
        $this->get_registry()->add_adapter(
            'I_Display_Type_Controller',
            'A_NextGen_Basic_Singlepic_Controller',
            $this->module_id
        );

        // Provides validation for the display type
//        $this->get_registry()->add_adapter(
//            'I_Display_Type',
//            'A_NextGen_Basic_Singlepic'
//       );

		// Provides default values for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_Singlepic_Mapper'
		);
    }
}

new M_NextGen_Basic_Singlepic();
