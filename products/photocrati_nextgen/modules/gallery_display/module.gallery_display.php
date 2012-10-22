<?php

/***
	{
		Module: photocrati-gallery_display,
		Depends: { photocrati-lazy_resources, photocrati-simple_html_dom }
	}
***/

class M_Gallery_Display extends C_Base_Module
{
	var $display_settings_page_name     = 'ngg_display_settings';
	var $controller                     = NULL;
    var $renderer                       = NULL;

	function define()
	{
		parent::define(
			'photocrati-gallery_display',
			'Gallery Display',
			'Provides the ability to display gallery of images',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);

		$this->add_mixin('Mixin_MVC_Controller_Rendering');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->controller   = $this->get_registry()->get_utility('I_Display_Settings_Controller');
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
		// This utility provides a controller that renders the
		// Display Settings page, used to control global values for
		// all display types
		$this->get_registry()->add_utility(
			'I_Display_Settings_Controller',
			'C_Display_Settings_Controller'
		);

		// This utility provides a controller to render the settings form
		// for a display type, or render the front-end of a display type
		$this->get_registry()->add_utility(
			'I_Display_Type_Controller',
			'C_Display_Type_Controller'
		);

		// This utility provides a datamapper for Display Types
		$this->get_registry()->add_utility(
			'I_Display_Type_Mapper',
			'C_Display_Type_Mapper'
		);

		// This utility provides a datamapper for Displayed Galleries. A
		// displayed gallery is the association between some entities (images
		//or galleries) and a display type
		$this->get_registry()->add_utility(
			'I_Displayed_Gallery_Mapper',
			'C_Displayed_Gallery_Mapper'
		);

        // This utility provides the capabilities of rendering a display type
        $this->get_registry()->add_utility(
            'I_Displayed_Gallery_Renderer',
            'C_Displayed_Gallery_Renderer'
        );
	}


	/**
	 * Registers adapters required for this module
	 */
	function _register_adapters()
	{
		// Provides factory methods for creating display type and
		// displayed gallery instances
		$this->get_registry()->add_adapter(
			'I_Component_Factory', 'A_Gallery_Display_Factory'
		);

		// Enqueues resources required for the Display Settings page
		$this->get_registry()->add_adapter(
			'I_NextGen_Backend_Controller',
			'A_Display_Settings_Page_Resources'
		);

        // plugin deactivation routine
        $this->get_registry()->add_adapter('I_NextGen_Deactivator', 'A_Gallery_Display_Deactivation');
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
		// Add the display settings page to wp-admin
		add_action('admin_menu', array(&$this, 'add_display_settings_page'), 900);

		// Enqueues static resources required
		if (is_admin()) {
			add_action(
				'admin_enqueue_scripts',
				array(&$this, 'enqueue_resources'),
				1
			);
		}

		// Add a shortcode for displaying galleries
        $this->renderer     = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
		add_shortcode('ngg_images', array(&$this->renderer, 'display_images'));

        // wrap the old nextgen tags to call our display_images()
        add_shortcode('imagebrowser', array(&$this, 'wrap_shortcode_imagebrowser'));
        add_shortcode('nggallery',    array(&$this, 'wrap_shortcode_nggallery'));
        add_shortcode('nggtags',      array(&$this, 'wrap_shortcode_nggtags'));
        add_shortcode('random',       array(&$this, 'wrap_shortcode_random'));
        add_shortcode('recent',       array(&$this, 'wrap_shortcode_recent'));
        add_shortcode('singlepic',    array(&$this, 'wrap_shortcode_singlepic'));
        add_shortcode('tagcloud',     array(&$this, 'wrap_shortcode_tagcloud'));
        add_shortcode('thumb',        array(&$this, 'wrap_shortcode_thumb'));
        add_shortcode('album',        array(&$this, 'wrap_shortcode_album'));
        add_shortcode('slideshow',    array(&$this, 'wrap_shortcode_slideshow'));

        add_action('init', array(&$this, 'serve_alternative_view_request'));
	}


	/**
	 * A display type can be forced for all galleries by specifying the
	 * display type to use in the url segment. We call these 'alternative views'.
	 *
	 * To force a particular display type to be used for the current request,
	 * the following url segment must be appended: /nggallery/[display_type_name]
	 *
	 * This functionality is required to maintain the integration between the
	 * NextGen Basic Slideshow and NextGen Basic Thumbnails display types, that
	 * NextGen Legacy introduced.
	 * @return null
	 */
    function serve_alternative_view_request()
    {
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			if (preg_match("/nggallery\/([\w_-]+)$/", $uri, $match)) {
				$_SERVER['REQUEST_URI'] = str_replace($match[0], '', $uri);
				$_SERVER['NGGALLERY'] = $match[1];
			}
		}
    }

	/**
	 * Adds the display settings page to wp-admin
	 */
	function add_display_settings_page()
	{
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Gallery & Album Settings'),
			_('Gallery Settings'),
			'NextGEN Manage gallery',
			$this->display_settings_page_name,
			array(&$this->controller, 'index_action')
		);
	}


	/**
	 * Enqueues static resources for the Display Settings Page
	 */
	function enqueue_resources()
	{
        // for tooltip styling
        if (isset($_GET['page']) && $_GET['page'] == 'nggallery-manage-gallery')
        {
            wp_enqueue_style('nggadmin', $this->static_url('nextgen_display_settings_page.css'));
        }
	}


    /**
     * Short-cut for rendering an album
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_album($params, $inner_content=NULL)
    {
        $params['source']           = $this->_get_param('source', 'albums', $params);
        $params['container_ids']    = $this->_get_param('id', NULL, $params);
        $params['display_type']     = $this->_get_param('display_type', PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM, $params);
        unset($params['id']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering an imagebrowser
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_imagebrowser($params, $inner_content=NULL)
    {
        $params['image_ids']    = $this->_get_param('id', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_imagebrowser', $params);
        unset($params['id']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering an thumbnail gallery
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_nggallery($params, $inner_content=NULL)
    {
        $params['gallery_ids']     = $this->_get_param('id', NULL, $params);
        $params['display_type']    = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        if (isset($params['images']))
        {
            $params['images_per_page'] = $this->_get_param('images', NULL, $params);
        }
        unset($params['id']);
        unset($params['images']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering a thumbnail gallery based on tags
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_nggtags($params, $inner_content=NULL)
    {
        $params['tag_ids']      = $this->_get_param('gallery', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        unset($params['gallery']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering a thumbnail gallery with random images
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_random($params, $inner_content=NULL)
    {
        $params['source']             = $this->_get_param('source', 'random', $params);
        $params['images_per_page']    = $this->_get_param('max', NULL, $params);
        $params['disable_pagination'] = $this->_get_param('disable_pagination', TRUE, $params);
        $params['display_type']       = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);

        // inside if because Mixin_Displayed_Gallery_Instance_Methods->get_entities() doesn't handle NULL container_ids
        // correctly
        if (isset($params['id']))
        {
            $params['container_ids'] = $this->_get_param('id', NULL, $params);
        }

        unset($params['max']);
        unset($params['id']);

        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering a thumbnail gallery with recent images
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_recent($params, $inner_content=NULL)
    {
        $params['source']             = $this->_get_param('source', 'recent', $params);
        $params['images_per_page']    = $this->_get_param('max', NULL, $params);
        $params['disable_pagination'] = $this->_get_param('disable_pagination', TRUE, $params);
        $params['display_type']       = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);

        if (isset($params['id']))
        {
            $params['container_ids'] = $this->_get_param('id', NULL, $params);
        }

        unset($params['max']);
        unset($params['id']);

        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering an singlepic gallery
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_singlepic($params, $inner_content=NULL)
    {
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_singlepic', $params);
        $params['image_id'] = $this->_get_param('id', NULL, $params);
        $params['source'] = $this->_get_param('source', 'image', $params);
        unset($params['id']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering an slideshow
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_slideshow($params, $inner_content=NULL)
    {
        $params['gallery_ids']    = $this->_get_param('id', NULL, $params);
        $params['display_type']   = $this->_get_param('display_type', 'photocrati-nextgen_basic_slideshow', $params);
        $params['gallery_width']  = $this->_get_param('w', NULL, $params);
        $params['gallery_height'] = $this->_get_param('h', NULL, $params);
        unset($params['id'], $params['w'], $params['h']);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering a tagcloud
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_tagcloud($params, $inner_content=NULL)
    {
        $params['tagcloud']     = $this->_get_param('tagcloud', 'yes', $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_tagcloud', $params);
        return $this->renderer->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering a thumbnail gallery
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_thumb($params, $inner_content=NULL)
    {
        $params['entity_ids']   = $this->_get_param('id', NULL, $params);
        $params['source']       = $this->_get_param('source', 'galleries', $params);
        $params['display_type'] = $this->_get_param('display_type', 'photocrati-nextgen_basic_thumbnails', $params);
        unset($params['id']);
        return $this->renderer->display_images($params, $inner_content);
    }

    /**
     * Gets a value from the parameter array, and if not available, uses the default value
     * @param string $name
     * @param mixed $default
     * @param array $params
     * @return mixed
     */
    function _get_param($name, $default, $params)
    {
        return (isset($params[$name])) ? $params[$name] : $default;
    }
}

new M_Gallery_Display();
