<?php

/***
	{
		Module: photocrati-gallery_display,
		Depends: { photocrati-lazy_resources, photocrati-simple_html_dom }
	}
***/

define(
	'PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL',
	real_admin_url('/attach_to_post/preview')
);

class M_Gallery_Display extends C_Base_Module
{
	var $display_settings_page_name     = 'ngg_display_settings';
	var $controller                     = NULL;
    var $renderer                       = NULL;
	var $attach_to_post_route           = 'wp-admin/attach_to_post';
	var $attach_to_post_tinymce_plugin  = 'NextGEN_AttachToPost';

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
		$this->_add_routes();
	}

	/**
	 * Registers routes with the MVC Router
	 */
	function _add_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');

		$router->add_route(
			__CLASS__ . '_Attach_to_Post',
			'C_Attach_to_Post_Controller',
			array('uri'=>$router->routing_pattern($this->attach_to_post_route))
		);
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
		// This utility provides a controller that renders the
		// Attach to Post interface, used to manage Displayed Galleries
		$this->get_registry()->add_utility(
			'I_Attach_To_Post_Controller',
			'C_Attach_To_Post_Controller'
		);

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
            'I_Display_Type_Renderer',
            'C_Display_Type_Renderer'
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

		// Provides AJAX actions for the Attach To Post interface
		$this->get_registry()->add_adapter(
			'I_Ajax_Controller',   'A_Attach_To_Post_Ajax'
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
		add_action('admin_menu', array(&$this, 'add_display_settings_page'), 999);

		// Enqueues static resources required
		if (is_admin()) {
			add_action(
				'admin_enqueue_scripts',
				array(&$this, 'enqueue_resources'),
				1
			);
		}

		// Add a shortcode for displaying galleries
        $this->renderer     = $this->get_registry()->get_utility('I_Display_Type_Renderer');
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

		// Add hook to delete displayed galleries when removed from a post
		add_action('pre_post_update', array(&$this, 'locate_stale_displayed_galleries'));
		add_action('before_delete_post', array(&$this, 'locate_stale_displayed_galleries'));
		add_action('post_updated',	array(&$this, 'cleanup_displayed_galleries'));
		add_action('after_delete_post', array(&$this, 'cleanup_displayed_galleries'));

		// Add hook to subsitute displayed gallery placeholders
		add_filter('the_content', array(&$this, 'substitute_placeholder_imgs'), 100, 1);
		remove_filter('the_content',    'wpautop');

        add_action('init', array(&$this, 'serve_alternative_view_request'));
	}

    /**
     * Substitutes the gallery placeholder content with the gallery type frontend
     * view, returns a list of static resources that need to be loaded
     * @param stdClass $post
     */
    function substitute_placeholder_imgs($content)
    {
        // Load html into parser
        $doc = new simple_html_dom();
        if ($content) {
            $doc->load($content);

            // Find all placeholder images
            $imgs = $doc->find("img[class='ngg_displayed_gallery']");
            if ($imgs) {

                // Get the displayed gallery mapper
                $mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

                // Substitute each image for the gallery type frontent content
                foreach ($imgs as $img) {

                    // The placeholder MUST have a gallery instance id
                    $preview_url = preg_quote(PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL, '/');
                    if (preg_match("/{$preview_url}\?id=(\d+)/", $img->src, $match)) {

                        // Find the displayed gallery
                        $displayed_gallery_id = $match[1];
                        $displayed_gallery = $mapper->find($displayed_gallery_id, TRUE);

                        // Get the content for the displayed gallery
                        $content = '<p>'._('Invalid Displayed Gallery').'</p>';
                        if ($displayed_gallery) {
                            $content = $this->renderer->render_displayed_gallery($displayed_gallery, TRUE);
                        }

                        // Replace the placeholder with the displayed gallery content
                        $img->outertext = $this->compress_html($content);
                    }
                }
                $content = (string)$doc->save();
            }
            return $content;
        }
    }

    //  this function gets rid of tabs, line breaks, and white space
    function compress_html($html)
    {
        $html = preg_replace("/>\s+/", ">", $html);
        $html = preg_replace("/\s+</", "<", $html);
        $html = preg_replace("/<!--(?:(?!-->).)*-->/m", "", $html);
        return $html;
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
		// Enqueue resources needed at post/page level
		if (preg_match("/\/wp-admin\/(post|post-new)\.php$/", $_SERVER['SCRIPT_NAME'])) {
			$this->_enqueue_tinymce_resources();
		}

		elseif (isset($_REQUEST['attach_to_post']) OR
		  (isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'nggallery') !== FALSE)) {
			wp_enqueue_script('iframely', $this->static_url('iframely.js'));
			wp_enqueue_style('iframely', $this->static_url('iframely.css'));
		}

        // for tooltip styling
        if (isset($_GET['page']) && $_GET['page'] == 'nggallery-manage-gallery')
        {
            wp_enqueue_style('nggadmin', $this->static_url('nextgen_display_settings_page.css'));
        }
	}

	/**
	 * Enqueues resources needed by the TinyMCE editor
	 */
	function _enqueue_tinymce_resources()
	{
		// Registers our tinymce button and plugin for attaching galleries
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_buttons', array(&$this, 'add_attach_to_post_button'));
                add_filter('mce_external_plugins', array(&$this, 'add_attach_to_post_tinymce_plugin'));
            }
        }
	}

	/**
	 * Adds a TinyMCE button for the Attach To Post plugin
	 * @param array $buttons
	 * @returns array
	 */
	function add_attach_to_post_button($buttons)
	{
		array_push(
            $buttons,
            'separator',
            $this->attach_to_post_tinymce_plugin
        );
        return $buttons;
	}


	/**
	 * Adds the Attach To Post TinyMCE plugin
	 * @param array $plugins
	 * @return array
	 * @uses mce_external_plugins filter
	 */
	function add_attach_to_post_tinymce_plugin($plugins)
	{
		$plugins[$this->attach_to_post_tinymce_plugin] = $this->static_url(
			'ngg_attach_to_post_tinymce_plugin.js'
		);

		return $plugins;
	}


	/**
	 * Locates the ids of displayed galleries that have been
	 * removed from the post, and flags then for cleanup (deletion)
	 * @global array $displayed_galleries_to_cleanup
	 * @param int $post_id
	 */
	function locate_stale_displayed_galleries($post_id)
	{
		global $displayed_galleries_to_cleanup;
		$displayed_galleries_to_cleanup = array();
		$post = get_post($post_id);
		$preview_url = preg_quote(PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL, '/');
		if (preg_match_all("/{$preview_url}\?id=(\d+)/", html_entity_decode($post->post_content), $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$preview_url = preg_quote($match[0], '/');
				// The post was edited, and the displayed gallery placeholder was removed
				if (isset($_REQUEST['post_content']) && (!preg_match("/{$preview_url}/", $_POST['post_content']))) {
					$displayed_galleries_to_cleanup[] = intval($match[1]);
				}
				// The post was deleted
				elseif (!isset($_REQUEST['action'])) {
					$displayed_galleries_to_cleanup[] = intval($match[1]);
				}
			}
		}
	}

	/**
	 * Deletes any displayed galleries that are no longer associated with
	 * a post/page
	 * @global array $displayed_galleries_to_cleanup
	 * @param int $post_id
	 */
	function cleanup_displayed_galleries($post_id)
	{
		global $displayed_galleries_to_cleanup;
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		foreach ($displayed_galleries_to_cleanup as $id) $mapper->destroy($id);
	}


    /**
     * Short-cut for rendering an album
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_album($params, $inner_content=NULL)
    {
        // not yet implemented
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
        $this->object->display_images($params, $inner_content);
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
        $this->object->display_images($params, $inner_content);
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
        $this->object->display_images($params, $inner_content);
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

        // inside if because Mixin_Displayed_Gallery_Instance_Methods->get_images() doesn't handle NULL container_ids
        // correctly
        if (isset($params['id']))
        {
            $params['container_ids'] = $this->_get_param('id', NULL, $params);
        }

        unset($params['max']);
        unset($params['id']);

        $this->object->display_images($params, $inner_content);
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

        $this->object->display_images($params, $inner_content);
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
        unset($params['id']);
        $this->object->display_images($params, $inner_content);
    }


    /**
     * Short-cut for rendering an slideshow
     * @param array $params
     * @param null $inner_content
     * @return string
     */
    function wrap_shortcode_slideshow($params, $inner_content=NULL)
    {
        // not yet implemented
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
        $this->object->display_images($params, $inner_content);
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
        $this->object->display_images($params, $inner_content);
    }
}

new M_Gallery_Display();
