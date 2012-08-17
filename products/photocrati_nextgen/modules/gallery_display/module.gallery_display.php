<?php

/***
	{
		Module: photocrati-gallery_display,
		Depends: { photocrati-lazy_resources }
	}
***/

class M_Gallery_Display extends C_Base_Module
{
	var $display_settings_page_name = 'ngg_display_settings';
	var $controller = NULL;
	var $attach_to_post_route = 'wp-admin/attach_to_post';
	var $attach_to_post_tinymce_plugin = 'NextGEN_AttachToPost';

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

		$this->add_mixin('Mixin_Render_Display_Type');
		$this->add_mixin('Mixin_MVC_Controller_Rendering');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->controller = $this->get_registry()->get_utility('I_Display_Settings_Controller');
		$this->_add_routes();
	}

	/**
	 * Registers routes with the MVC Router
	 */
	function _add_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$router->add_route(
			__CLASS__,
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
				array(&$this, 'enqueue_resources')
			);
		}

		// Add a shortcode for displaying galleries
		add_shortcode('ngg_images', array(&$this, 'display_images'));
	}


	/**
	 * Adds the display settings page to wp-admin
	 */
	function add_display_settings_page()
	{
		add_submenu_page(
			NGGFOLDER,
			_('NextGEN Display Settings'),
			_('Display Settings'),
			'NextGEN Manage gallery',
			$this->display_settings_page_name,
			array(&$this->controller, 'index')
		);
	}


	/**
	 * Enqueues static resources for the Display Settings Page
	 */
	function enqueue_resources()
	{
		// Enqueue resources needed for the Display Settings Page
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == $this->display_settings_page_name) {
			$this->_enqueue_display_settings_resources();
		}

		// Enqueue resources needed at post/page level
		elseif ($_SERVER['SCRIPT_NAME'] == '/wp-admin/post.php') {
			$this->_enqueue_tinymce_resources();
		}

		elseif (isset($_REQUEST['attach_to_post'])) {
			wp_enqueue_script('iframely', $this->static_url('iframely.js'));
			wp_enqueue_style('iframely', $this->static_url('iframely.css'));
		}
	}


	/**
	 * Enqueues static resources needed for the Display Settings page
	 */
	function enqueue_display_settings_resources()
	{
		wp_enqueue_script(
			'nextgen_display_settings_page',
			PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js/nextgen_display_settings_page.js',
			array('jquery-ui-accordion'),
			$this->module_version
		);

		// There are many jQuery UI themes available via Google's CDN:
		// See: http://stackoverflow.com/questions/820412/downloading-jquery-css-from-googles-cdn
		wp_enqueue_style(
			'jquery-ui-south-street',
			(is_ssl()?'https':'http').'://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/south-street/jquery-ui.css',
			array(),
			'1.7.0'
		);

		wp_enqueue_style(
			'nextgen_display_settings_page',
			PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/css/nextgen_display_settings_page.css'
		);
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
		$plugins[$this->attach_to_post_tinymce_plugin] = implode('/', array(
			PHOTOCRATI_GALLERY_MODULE_URL,
			basename(__DIR__),
			'js/ngg_attach_to_post_tinymce_plugin.js'
		));

		return $plugins;
	}
}

new M_Gallery_Display();