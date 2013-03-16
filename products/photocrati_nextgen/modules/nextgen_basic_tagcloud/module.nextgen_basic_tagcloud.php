<?php

/***
{
        Module:     photocrati-nextgen_basic_tagcloud,
        Depends:    { photocrati-nextgen_gallery_display }
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

	function initialize()
	{
		parent::initialize();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG, NEXTGEN_BASIC_TAG_CLOUD_MODULE_NAME
		);
	}


    function _register_adapters()
    {
        // Installs the display type
        $this->get_registry()->add_adapter(
            'I_Installer',
            'A_NextGen_Basic_Tagcloud_Installer'
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

		// Adds a display settings form
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_TagCloud_Form',
			$this->module_id
		);
    }

	function _register_hooks()
	{
		add_action('tagcloud', array(&$this, 'render_shortcode'));
	}

	/**
     * Short-cut for rendering a thumbnail gallery based on tags
     * @param array $params
     * @param null $inner_content
     * @return string
     */
	function render_shortcode($params, $inner_content=NULL)
    {
	    $params['tagcloud']     = $this->_get_param('tagcloud', 'yes', $params);
        $params['source']       = $this->_get_param('source', 'tags', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_tagcloud', $params);

		$renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
    }

    function set_file_list()
    {
        return array(
            'adapter.nextgen_basic_tagcloud.php',
            'adapter.nextgen_basic_tagcloud_controller.php',
            'adapter.nextgen_basic_tagcloud_form.php',
            'adapter.nextgen_basic_tagcloud_installer.php',
            'adapter.nextgen_basic_tagcloud_mapper.php',
            'adapter.nextgen_basic_tagcloud_routes.php',
            'adapter.nextgen_basic_tagcloud_urls.php'
        );
    }
}

new M_NextGen_Basic_Tagcloud();
