<?php

/***
	{
		Module: photocrati-admin,
                Depends: { photocrati-mvc, photocrati-resource_loader, photocrati-base }
	}
***/
define('PHOTOCRATI_GALLERY_ADMIN_MOD_URL', path_join(PHOTOCRATI_GALLERY_MODULE_URL, basename(dirname(__FILE__))));
define('PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL', path_join(PHOTOCRATI_GALLERY_ADMIN_MOD_URL, 'static'));
define('PHOTOCRATI_GALLERY_ADMIN_AJAX_ROUTING_PATTERN', "/\/wp-admin\/ajax_handler\/?([^\?]*)/");
define('PHOTOCRATI_GALLERY_ADMIN_AJAX_URL', admin_url('ajax_handler'));


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
        remove_submenu_page(NGGFOLDER, NGGFOLDER);
        remove_submenu_page(NGGFOLDER, 'nggallery-add-gallery');
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
        
        //add_submenu_page(NGGFOLDER, $galleries_menu, $galleries_menu, 'NextGEN Manage gallery', 'pc-galleries', array($this->_controller, 'galleries'));
        //add_submenu_page(NGGFOLDER, $add_galleries_menu, $add_galleries_menu, 'NextGEN Upload images', 'pc-add-gallery');
        add_submenu_page(NGGFOLDER, $gallery_settings_menu, $gallery_settings_menu, 'NextGEN Change options', 'pc-gallery-settings', array($this->_controller, 'gallery_settings'));
        add_submenu_page(NGGFOLDER, $albums_menu, $albums_menu, 'NextGEN Edit album', 'pc-albums');
        add_submenu_page(NGGFOLDER, $other_options_menu, $other_options_menu, 'NextGEN Change options', 'pc-other-options', array($this->_controller, 'other_options'));
        add_submenu_page(NGGFOLDER, $upgrade_menu, $upgrade_menu, 'Administrator', 'pc-upgrade');
        add_submenu_page(NGGFOLDER, $upgrade_menu, $upgrade_menu, 'NextGen Upgrade', 'nextgen-upgrade');
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
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $this->_controller = $factory->create('admin_controller');
        $this->_add_routes();
    }
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Component_Factory', 'A_Admin_Factory');
    }
    

    function _register_hooks()
    {
        add_action('admin_menu', array(&$this, 'admin_menu'));
        $this->_enqueue();
    }
    
    
    function _add_routes()
    {
        $router = $this->_registry->get_singleton_utility('I_Router');
        $router->add_route(__CLASS__, 'C_Ajax_Handler', array(
            'uri'=>PHOTOCRATI_GALLERY_ADMIN_AJAX_ROUTING_PATTERN
        ));
    }
    
    
    function _enqueue()
    {
        $dequeue = array(
            'jquery',
            'jquery-ui-core',
            'jquery-ui-tabs',
            'jquery-ui-sortable',
            'jquery-ui-draggable',
            'jquery-ui-droppable',
            'jquery-ui-selectable',
            'jquery-ui-resizable',
            'jquery-ui-dialog'
        );
        
        foreach ($dequeue as $script) {
            if (in_array($script, array('jquery', 'jquery-ui-core'))) {
                wp_deregister_script($script);
            }
            wp_dequeue_script($script);
        }
        
        
        wp_register_script(
            'jquery-ui-core', 
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL, 
                'jquery-ui-1.8.16.custom/js/jquery-ui-1.8.16.custom.min.js'
            ),
            array('jquery')
        );
        
        wp_register_script(
            'jquery',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'jquery-ui-1.8.16.custom/js/jquery-1.6.2.min.js'
            )
        );
        
        wp_register_script(
            'pc-admin', 
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'admin.js'
            ),
            array('jquery-ui-core')
        );
        
        
        wp_register_style(
            'pc-admin',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'admin.css'
            )
        );
        
        wp_deregister_script('tiptip');
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
        
        
        wp_deregister_style('jquery-ui-core');
        wp_register_style(
            'jquery-ui-core',
            path_join(
                PHOTOCRATI_GALLERY_ADMIN_MOD_STATIC_URL,
                'jquery-ui-1.8.16.custom/css/smoothness/jquery-ui-1.8.16.custom.css'
            )
        );
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('pc-admin');
        wp_enqueue_style('jquery-ui-core');
        wp_enqueue_script('tiptip');
        wp_enqueue_style('tiptip');
        wp_enqueue_script('jquery-colorpicker');
        wp_enqueue_style('jquery-colorpicker');
        wp_enqueue_style('pc-admin');
    }
}
new M_Photocrati_Admin();
