<?php

/***
{
		Module:		photocrati-nextgen_basic_thumbnails,
		Depends:	{ photocrati-gallery_display }
}
 ***/

define(
	'NEXTGEN_GALLERY_BASIC_THUMBNAILS',
	'photocrati-nextgen_basic_thumbnails'
);

class M_NextGen_Basic_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			NEXTGEN_GALLERY_BASIC_THUMBNAILS,
			'NextGen Basic Thumbnails',
			'Provides a thumbnail gallery for NextGEN Gallery',
			'1.9.6',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

    public function initialize()
    {
        parent::initialize();
    }

	function _register_adapters()
	{
		// Provides additional routing
		$this->get_registry()->add_adapter(
			'I_Router',
			'A_NextGen_Basic_Thumbnail_Routes'
		);

		// Provides NextGen Basic Thumbnail URLs
		$this->get_registry()->add_adapter(
			'I_Routing_App',
			'A_NextGen_Basic_Thumbnail_Urls'
		);

		// Installs the display type
		$this->get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_NextGen_Basic_Thumbnails_Activation'
		);

		// Provides settings fields and frontend rendering
		$this->get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_Basic_Thumbnails_Controller',
			$this->module_id
		);

		// Provides validation for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type',
			'A_NextGen_Basic_Thumbnails'
		);

		// Provides default values for the display type
		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_Basic_Thumbnails_Mapper'
		);

		$this->get_registry()->add_adapter(
			'I_NextGen_Backend_Controller',
			'A_NextGen_Basic_Thumbnails_Resources'
		);

		// Provides AJAX pagination actions required by the display type
        $this->get_registry()->add_adapter(
            'I_Ajax_Controller',
            'A_Ajax_Pagination_Actions'
        );
	}

	function _register_hooks()
	{
		add_shortcode('nggallery', array(&$this, 'render'));
		add_shortcode('nggtags',   array(&$this, 'render_based_on_tags'));
	}

	/**
     * Short-cut for rendering an thumbnail gallery
     * @param array $params
     * @param null $inner_content
     * @return string
     */
	function render($params, $inner_content=NULL)
    {
        $params['gallery_ids']     = $this->_get_param('id', NULL, $params);
        $params['display_type']    = $this->_get_param('display_type', NEXTGEN_GALLERY_BASIC_THUMBNAILS, $params);
        if (isset($params['images']))
        {
            $params['images_per_page'] = $this->_get_param('images', NULL, $params);
        }
        unset($params['id']);
        unset($params['images']);
        return $this->renderer->display_images($params, $inner_content);
    }

	function render_based_on_tags($params, $inner_content=NULL)
    {
        $params['tag_ids']      = $this->_get_param('gallery', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        unset($params['gallery']);
        return $this->renderer->display_images($params, $inner_content);
    }
}

new M_NextGen_Basic_Thumbnails();
