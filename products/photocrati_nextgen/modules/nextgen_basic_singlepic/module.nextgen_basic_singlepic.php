<?php

/***
{
        Module:     photocrati-nextgen_basic_singlepic,
        Depends:    { photocrati-nextgen_gallery_display }
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

	function initialize()
	{
		parent::initialize();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG, NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME
		);
	}


    function _register_adapters()
    {
        // Installs the display type
        $this->get_registry()->add_adapter(
            'I_Installer',
            'A_NextGen_Basic_SinglePic_Installer'
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

		// Provides the display settings form for the SinglePic display type
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_SinglePic_Form',
			$this->module_id
		);
    }

	function _register_hooks()
	{
		add_shortcode('singlepic',    array(&$this, 'render_singlepic'));
	}

    /**
     * Gets a value from the parameter array, and if not available, uses the default value
     *
     * @param string $name
     * @param mixed $default
     * @param array $params
     * @return mixed
     */
    function _get_param($name, $default, $params)
    {
        return (isset($params[$name])) ? $params[$name] : $default;
    }

	function render_singlepic($params, $inner_content=NULL)
	{
		$params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_singlepic', $params);
        $params['image_ids'] = $this->_get_param('id', NULL, $params);
        unset($params['id']);

		$renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
	}

    function set_file_list()
    {
        return array(
            'adapter.nextgen_basic_singlepic.php',
            'adapter.nextgen_basic_singlepic_controller.php',
            'adapter.nextgen_basic_singlepic_form.php',
            'adapter.nextgen_basic_singlepic_installer.php',
            'adapter.nextgen_basic_singlepic_mapper.php'
        );
    }
}

new M_NextGen_Basic_Singlepic();
