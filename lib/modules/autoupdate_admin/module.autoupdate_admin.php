<?php

/***
	{
		Module: photocrati-auto_update-admin,
		Depends: { photocrati-auto_update, photocrati-admin }
	}
***/

define('PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_URL', path_join(PHOTOCRATI_GALLERY_MODULE_URL, basename(dirname(__FILE__))));
define('PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL', path_join(PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_URL, 'static'));

class M_AutoUpdate_Admin extends C_Base_Module
{
		var $_updater = null;
		var $_update_list = null;
		
    function initialize()
    {
        parent::initialize(
            'photocrati-auto_update-admin',
            'Photocrati Auto Update Admin',
            "Provides an AJAX admin interface to sequentially and progressively download and install updates",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
        
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $this->_controller = $factory->create('autoupdate_admin_controller');
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Component_Factory', 'A_AutoUpdate_Admin_Factory');
        $this->_registry->add_adapter('I_Ajax_Handler', 'A_AutoUpdate_Admin_Ajax');
    }
    
    
    function _register_hooks()
    {
        add_action('admin_init', array($this, 'admin_init'));
				add_action('admin_menu', array($this, 'admin_menu'));
				
        wp_register_script(
            'pc-autoupdate-admin', 
            path_join(
                PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL,
                'admin.js'
            ),
            array('jquery-ui-core')
        );
				
        wp_register_style(
            'pc-autoupdate-admin', 
            path_join(
                PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL,
                'admin.css'
            )
        );
        
        wp_enqueue_script('pc-autoupdate-admin');
        wp_enqueue_style('pc-autoupdate-admin');
    }
    
    
    function _get_update_list()
    {
    	if ($this->_update_list == null)
    	{
		    $this->_updater = $this->_registry->get_module('photocrati-auto_update');
		    
		    if ($this->_updater != null)
		    {
		    	// XXX this should be cached and checked only once in a while
		    	$return = $this->_updater->check_product_list();
		    	
		    	if ($return != null && is_array($return))
		    	{
		    		$update_list = array();
		    		
		    		foreach ($return as $item)
		    		{
		    			if (in_array($item['action'], array('module-add', 'module-remove', 'module-update')))
		    			{
		    				$update_list[] = $item;
		    			}
		    		}
		    		
		    		$this->_update_list = $update_list;
		    	}
		    }
    	}
    	
    	return $this->_update_list;
    }
    
    
    function _get_text_list()
    {
    	return array(
    		'no_updates' => __('No updates available.'),
    		'updates_available' => __('Updates available, {1} updates of {0} will be installed.'),
    		'updates_sizes' => __('Update size is {0} and a total of <b>{1}</b> will be downloaded.'),
    		'updates_expired' => __('Note: {0} of the available updates can\'t be installed because your subscription is expired.'),
    		'updates_renew' => __('Renew my subscription.'),
    		'updater_status_done' => __('Done.'),
    		'updater_status_preparing' => __('Preparing upgrade process...'),
    		'updater_status_stage_download' => __('Downloading package {1} of {0}...'),
    		'updater_status_stage_install' => __('Installing package {1} of {0}...'),
    		'updater_status_stage_activate' => __('Activating packages...'),
    		'updater_status_stage_cleanup' => __('Cleaning up...'),
    		'updater_status_cancel' => __('Update was canceled.'),
    		'updater_status_error' => __('An error occurred during the operation ({0}).')
    	);
    }
    
    
    function admin_init()
    {
        // XXX use WP built-in ajax handler?
        //array('ajaxurl' => admin_url('admin-ajax.php'));
        wp_localize_script('pc-autoupdate-admin', 'Photocrati_AutoUpdate_Admin', array('ajaxurl' => admin_url('ajax_handler'), 'update_list' => json_encode($this->_get_update_list()), 'text_list' => json_encode($this->_get_text_list())));
    }
    
    
    function admin_menu()
    {
        if ($this->_get_update_list() != null)
        {
					add_submenu_page('tools.php', __('Update'), __('Update'), 'update_plugins', $this->module_id, array($this->_controller, 'admin_page'));
        }
    }
}
new M_AutoUpdate_Admin();
