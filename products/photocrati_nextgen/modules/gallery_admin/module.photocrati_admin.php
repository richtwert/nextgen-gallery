<?php

/***
	{
		Module: photocrati-admin
	}
***/
define('PHOTOCRATI_GALLERY_ADMIN_MOD_URL', path_join(PHOTOCRATI_GALLERY_MODULE_URL, basename(dirname(__FILE__))));
define('PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL', path_join(PHOTOCRATI_GALLERY_ADMIN_MOD_URL, 'static'));


class M_Photocrati_Admin_Menu extends Mixin
{
    /**
     * We remove the NextGEN menu items to replace with our own. We could have
     * just removed the top-level menu, but then third-party plugins might not
     * work as expected. We want to try to remain backwards compatible with
     * NextGen as much as possible
     *
     * TODO: If we own NextGEN, we can put this in nggAdminPanel::add_menu()
     */
    function admin_menu()
    {
//        remove_submenu_page(NGGFOLDER, NGGFOLDER);
//        remove_submenu_page(NGGFOLDER, 'nggallery-add-gallery');
//        remove_submenu_page(NGGFOLDER, 'nggallery-manage-gallery');
//        remove_submenu_page(NGGFOLDER, 'nggallery-manage-album');
//        remove_submenu_page(NGGFOLDER, 'nggallery-tags');
        //remove_submenu_page(NGGFOLDER, 'nggallery-options');
        //remove_submenu_page(NGGFOLDER, 'nggallery-style');
        //remove_submenu_page(NGGFOLDER, 'nggallery-roles');
        //remove_submenu_page(NGGFOLDER, 'nggallery-about');
        //remove_submenu_page(NGGFOLDER, 'nggallery-setup');

        $galleries_menu         = __('Galleries', PHOTOCRATI_GALLERY_I8N_DOMAIN);
        $add_galleries_menu     = __('Add Gallery / Images', PHOTOCRATI_GALLERY_I8N_DOMAIN);
        $gallery_settings_menu  = __('Gallery Settings', PHOTOCRATI_GALLERY_I8N_DOMAIN);
        $albums_menu            = __('Albums', PHOTOCRATI_GALLERY_I8N_DOMAIN);
        $other_options_menu     = __('Other Options', PHOTOCRATI_GALLERY_I8N_DOMAIN);
        $upgrade_menu           = __('Upgrade', PHOTOCRATI_GALLERY_I8N_DOMAIN);

        //add_submenu_page(NGGFOLDER, $galleries_menu, $galleries_menu, PHOTOCRATI_GALLERY_MANAGE_GALLERY_CAP, 'pc-galleries', array($this->_controller, 'galleries'));
        //add_submenu_page(NGGFOLDER, $add_galleries_menu, $add_galleries_menu, PHOTOCRATI_GALLERY_UPLOAD_IMAGE_CAP, 'pc-add-gallery');
        add_submenu_page(NGGFOLDER, $gallery_settings_menu, $gallery_settings_menu, PHOTOCRATI_GALLERY_CHANGE_OPTIONS_CAP, 'pc-gallery-settings', array($this->_controller, 'gallery_settings'));
        // XXX add handler
        //add_submenu_page(NGGFOLDER, $albums_menu, $albums_menu, PHOTOCRATI_GALLERY_MANAGE_ALBUM_CAP, 'pc-albums');
        //add_submenu_page(NGGFOLDER, $other_options_menu, $other_options_menu, PHOTOCRATI_GALLERY_CHANGE_OPTIONS_CAP, 'pc-other-options', array($this->_controller, 'other_options'));
        // XXX add handler
        //add_submenu_page(NGGFOLDER, $upgrade_menu, $upgrade_menu, 'Administrator', 'pc-upgrade');
        // XXX add handler
        //add_submenu_page(NGGFOLDER, $upgrade_menu, $upgrade_menu, 'NextGen Upgrade', 'nextgen-upgrade');
    }
}


/**
 * Depends on MVC Module
 */
class M_Photocrati_Admin extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-admin',
            'Photocrati Admin Interface',
            'Provides an easy-to-use interface for managing NextGEN galleries',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );

        $this->add_mixin('M_Photocrati_Admin_Menu');
        $this->implement('I_Photocrati_Admin_Module');
    }


    function initialize()
    {
        $factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
        $this->_controller = $factory->create('admin_controller');
		$this->_add_routes();
    }

    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Admin_Factory');
		$this->_get_registry()->add_adapter('I_MVC_Controller', 'A_Display_Validation_Errors');
    }


    function _register_hooks()
    {
        add_action('init', array(&$this, 'enqueue_scripts'));
		add_action('admin_menu', array(&$this, 'admin_menu'), 99);
    }


    function _add_routes()
    {
        $router = $this->_get_registry()->get_singleton_utility('I_Router');
        $router->add_route(__CLASS__, 'C_Ajax_Handler', array(
            'uri'=>$router->routing_pattern('photocrati_admin/ajax')
        ));
    }


    function enqueue_scripts()
    {
		wp_register_script(
            'pc-admin',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'admin.js'
            ),
            array('jquery-ui-accordion')
        );

        wp_register_style(
            'pc-admin',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'admin.css'
            )
        );

        wp_register_script(
           'tiptip',
           path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'tiptip/jquery.tipTip.minified.js'
           ),
           array('jquery')
        );


        wp_register_style(
           'tiptip',
           path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'tiptip/tipTip.css'
           )
        );

        wp_register_script(
            'jquery-colorpicker',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'colorpicker/js/colorpicker.js'
           ),
           array('jquery')
        );

        wp_register_style(
            'jquery-colorpicker',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'colorpicker/css/colorpicker.css'
           )
        );

		wp_register_style(
			'jquery-ui-smoothness',
			path_join(
				PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
				'jquery-ui-smoothness-1.8.16.css'
			),
			array(),
			'1.8.16'
		);

		wp_enqueue_style('jquery-ui-smoothness');
		wp_enqueue_script('pc-admin');
        wp_enqueue_script('tiptip');
        wp_enqueue_style('tiptip');
        wp_enqueue_script('jquery-colorpicker');
        wp_enqueue_style('jquery-colorpicker');
        wp_enqueue_style('pc-admin');
    }
}
new M_Photocrati_Admin();
