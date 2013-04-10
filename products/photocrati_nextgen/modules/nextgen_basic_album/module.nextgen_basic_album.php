<?php

/*
{
    Module:		photocrati-nextgen_basic_album,
    Depends:  	{ photocrati-nextgen_gallery_display, photocrati-nextgen_basic_templates, photocrati-nextgen_pagination }
}
 */

define('NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM', 'photocrati-nextgen_basic_compact_album');
define('NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM', 'photocrati-nextgen_basic_extended_album');

class M_NextGen_Basic_Album extends C_Base_Module
{
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

	function initialize()
	{
		parent::initialize();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG,
			NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM
		);
		$form_manager->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG,
			NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM
		);
	}


    function _register_adapters()
    {
		// Add module activation
        $this->get_registry()->add_adapter(
			'I_Installer',
			'A_NextGen_Basic_Album_Installer'
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

		// Add a display settings form for each display type
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_Compact_Album_Form',
			NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM
		);
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_Extended_Album_Form',
			NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM
		);

        // Creates special parameter segments
        $this->get_registry()->add_adapter(
            'I_Routing_App',
            'A_NextGen_Basic_Album_Urls'
        );
    }

	function _register_hooks()
	{
		add_shortcode('album',        array(&$this, 'ngglegacy_shortcode'));
		add_shortcode('nggalbum',        array(&$this, 'ngglegacy_shortcode'));
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
     * Renders the shortcode for rendering an album
     * @param array $params
     * @param null $inner_content
     * @return string
     */
	function ngglegacy_shortcode($params, $inner_content=NULL)
    {
        $params['source']           = $this->_get_param('source', 'albums', $params);
        $params['container_ids']    = $this->_get_param('id', NULL, $params);
        $params['display_type']     = $this->_get_param('display_type', NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM, $params);

        unset($params['id']);

        $renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
        return $renderer->display_images($params, $inner_content);
    }

    function set_file_list()
    {
        return array(
            'adapter.nextgen_basic_album.php',
            'adapter.nextgen_basic_album_controller.php',
            'adapter.nextgen_basic_album_installer.php',
            'adapter.nextgen_basic_album_mapper.php',
            'adapter.nextgen_basic_album_routes.php',
            'adapter.nextgen_basic_album_urls.php',
            'adapter.nextgen_basic_compact_album_form.php',
            'adapter.nextgen_basic_extended_album_form.php',
            'mixin.nextgen_basic_album_form.php'
        );
    }
}


new M_NextGen_Basic_Album();