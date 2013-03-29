<?php
/***
{
	Module:		photocrati-nextgen_basic_imagebrowser,
	Depends:	{ photocrati-nextgen_gallery_display }
}
***/

define(
	'NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER',
	'photocrati-nextgen_basic_imagebrowser'
);

class M_NextGen_Basic_ImageBrowser extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_basic_imagebrowser',
			'NextGEN Basic ImageBrowser',
			'Provides the NextGEN Basic ImageBrowser Display Type',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function initialize()
	{
		parent::initialize();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG, NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER
		);
	}

	/**
	 * Register adapters required for the NextGen Basic ImageBrowser
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
		  'I_Display_Type_Mapper',		'A_NextGen_Basic_ImageBrowser_Mapper'
		);

		// Add validation for the display type
		$this->get_registry()->add_adapter(
		  'I_Display_Type',			    'A_NextGen_Basic_ImageBrowser'
		);

		// Add activation routine
		$this->get_registry()->add_adapter(
		  'I_Installer',				'A_NextGen_Basic_ImageBrowser_Installer'
		);

		// Add rendering logic
		$this->get_registry()->add_adapter(
		  'I_Display_Type_Controller', 'A_NextGen_Basic_ImageBrowser_Controller',
		  $this->module_id
		);

		// Add imagebrowser routes
		$this->get_registry()->add_adapter(
			'I_Routing_App',			'A_NextGen_Basic_ImageBrowser_Routes'
		);

		// Add imagebrowser ngglegacy-compatible urls
		$this->get_registry()->add_adapter(
			'I_Routing_App',			'A_NextGen_Basic_ImageBrowser_Urls'
		);

		// Provide the imagebrowser form
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_ImageBrowser_Form',
			$this->module_id
		);
	}

	function _register_hooks()
	{
		add_shortcode('imagebrowser', array(&$this, 'render_shortcode'));
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

	function render_shortcode($params, $inner_content=NULL)
    {
        $params['gallery_ids']  = $this->_get_param('id', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER, $params);

        unset($params['id']);

        $renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
    }

    function set_file_list()
    {
        return array(
            'adapter.nextgen_basic_imagebrowser.php',
            'adapter.nextgen_basic_imagebrowser_controller.php',
            'adapter.nextgen_basic_imagebrowser_form.php',
            'adapter.nextgen_basic_imagebrowser_installer.php',
            'adapter.nextgen_basic_imagebrowser_mapper.php',
            'adapter.nextgen_basic_imagebrowser_routes.php',
            'adapter.nextgen_basic_imagebrowser_urls.php'
        );
    }
}

new M_NextGen_Basic_ImageBrowser();