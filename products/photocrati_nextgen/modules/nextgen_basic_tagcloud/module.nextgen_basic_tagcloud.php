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

        // Provides the forms for the settings
        $this->get_registry()->add_adapter(
            'I_Form_Manager',
            'A_NextGen_Basic_TagCloud_Forms'
        );
    }

	function _register_hooks()
	{
		add_shortcode('tagcloud', array(&$this, 'render_shortcode'));
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

    function get_type_list()
    {
        return array(
            'A_Nextgen_Basic_Tagcloud' => 'adapter.nextgen_basic_tagcloud.php',
            'A_Nextgen_Basic_Tagcloud_Controller' => 'adapter.nextgen_basic_tagcloud_controller.php',
            'A_Nextgen_Basic_Tagcloud_Form' => 'adapter.nextgen_basic_tagcloud_form.php',
            'A_Nextgen_Basic_Tagcloud_Forms' => 'adapter.nextgen_basic_tagcloud_forms.php',
            'A_Nextgen_Basic_Tagcloud_Installer' => 'adapter.nextgen_basic_tagcloud_installer.php',
            'A_Nextgen_Basic_Tagcloud_Mapper' => 'adapter.nextgen_basic_tagcloud_mapper.php',
            'A_Nextgen_Basic_Tagcloud_Routes' => 'adapter.nextgen_basic_tagcloud_routes.php',
            'A_Nextgen_Basic_Tagcloud_Urls' => 'adapter.nextgen_basic_tagcloud_urls.php'
        );
    }
}

new M_NextGen_Basic_Tagcloud();
