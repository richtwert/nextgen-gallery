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

	function _register_hooks()
	{
		add_shortcode('singlepic',    array(&$this, 'render_singlepic'));
	}

	
	function render_singlepic($params, $inner_content=NULL)
	{
		$params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_singlepic', $params);
        $params['image_ids'] = $this->_get_param('id', NULL, $params);
        unset($params['id']);

		$renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
	}
}

new M_NextGen_Basic_Singlepic();
