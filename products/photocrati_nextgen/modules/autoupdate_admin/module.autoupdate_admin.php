<?php

/***
	{
		Module: photocrati-auto_update-admin,
		Depends: { photocrati-auto_update }
	}
***/

define('PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_URL', path_join(PHOTOCRATI_GALLERY_MODULE_URL, basename(dirname(__FILE__))));
define('PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL', path_join(PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_URL, 'static'));

class M_AutoUpdate_Admin extends C_Base_Module
{
		var $_updater = null;
		var $_update_list = null;
		
    function define()
    {
        parent::define(
            'photocrati-auto_update-admin',
            'Photocrati Auto Update Admin',
            "Provides an AJAX admin interface to sequentially and progressively download and install updates",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function initialize()
    {
        $factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
        $this->_controller = $factory->create('autoupdate_admin_controller');
    }
    
    
    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_AutoUpdate_Admin_Factory');
        $this->_get_registry()->add_adapter('I_Ajax_Handler', 'A_AutoUpdate_Admin_Ajax');
    }
    
    
    function _register_hooks()
    {
        add_action('admin_init', array($this, 'admin_init'));
				add_action('admin_menu', array($this, 'admin_menu'));
				add_action('wp_dashboard_setup', array($this, 'dashboard_setup'));
        
        if (is_admin())
        {
		      if (!interface_exists('I_Ajax_Handler')) {
		      	$this->_ajax_handler = new C_AutoUpdate_Admin_Ajax();
		      	
						add_action('wp_ajax_photocrati_autoupdate_admin_handle', array($this->_ajax_handler, 'handle_ajax'));
		      }
				
		      wp_register_script(
		          'jquery-ui-progressbar', 
		          path_join(
		              PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL,
		              'jqueryUI.progressbar.js'
		          ),
		          array('jquery-ui-core')
		      );
				
		      wp_register_script(
		          'pc-autoupdate-admin', 
		          path_join(
		              PHOTOCRATI_GALLERY_AUTOUPDATE_ADMIN_MOD_STATIC_URL,
		              'admin.js'
		          ),
		          array('jquery-ui-core', 'jquery-ui-progressbar', 'jquery-ui-dialog')
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
    }
    
    
    function _get_update_list()
    {
    	if ($this->_update_list == null)
    	{
		    $this->_updater = $this->_get_registry()->get_module('photocrati-auto_update');
		    
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
    		'updates_license_invalid' => __('Note: {0} of the available updates can\'t be installed because your license seems invalid or no license was installed in this instance.'),
    		'updates_license_get' => __('Install my license'),
    		'updates_expired' => __('Note: {0} of the available updates can\'t be installed because your subscription is expired.'),
    		'updates_renew' => __('Renew my subscription.'),
    		'updater_button_start' => __('Start Update'),
    		'updater_status_done' => __('Done.'),
    		// XXX note that because of how json_encode works we need to manually escape double quotes it seems
    		'updater_status_start' => __('Click \\"Start Update\\" to begin the upgrade process.'),
    		'updater_status_preparing' => __('Preparing upgrade process...'),
    		'updater_status_stage_download' => __('Downloading package {1} of {0}...'),
    		'updater_status_stage_install' => __('Installing package {1} of {0}...'),
    		'updater_status_stage_activate' => __('Activating packages...'),
    		'updater_status_stage_cleanup' => __('Cleaning up...'),
    		'updater_status_cancel' => __('Update was canceled.'),
    		'updater_status_error' => __('An error occurred during the operation ({0}).'),
    		'updater_logger_title' => __('Show Update Log'),
    		'updater_logger_download' => __('Download Update Log')
    	);
    }
    
    function get_update_page_url()
    {
    	// XXX make index.php automatic? maybe store it when creating subpage
    	return admin_url('index.php?page=photocrati-auto_update-admin');
    }
    
    
    function admin_init()
    {
			// XXX always use WP built-in ajax handler?
			$ajaxurl = admin_url('ajax_handler');

			if (!interface_exists('I_Ajax_Handler')) {
				$ajaxurl = admin_url('admin-ajax.php');
			}

			wp_localize_script('pc-autoupdate-admin', 'Photocrati_AutoUpdate_Admin_Settings', array('ajaxurl' => $ajaxurl, 'actionSec' => wp_create_nonce('pc-autoupdate-admin-nonce'), 'request_site' => base64_encode(admin_url()), 'update_list' => json_encode($this->_get_update_list()), 'text_list' => json_encode($this->_get_text_list())));

			if ((isset($_POST['action']) && $_POST['action'] == 'photocrati_autoupdate_admin_handle'))
			{
				ob_start();
			}
    }
    
    
    function admin_menu()
    {
    	$list = $this->_get_update_list();
    	
      if ($list != null)
      {
				add_submenu_page('index.php', __('Photocrati Updates'), __('Photocrati Updates') . ' <span class="update-plugins"><span class="update-count">' . count($list) . '</span></span>', 'update_plugins', $this->module_id, array($this->_controller, 'admin_page'));
      }
    }
    
    function dashboard_setup()
    {
   		wp_add_dashboard_widget('photocrati_admin_dashboard_widget', __('Welcome to Photocrati'), array($this, 'dashboard_widget'));

			global $wp_meta_boxes;
			
			if (isset($wp_meta_boxes['dashboard']['normal']['core']))
			{
				$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
				$widget_backup = array('photocrati_admin_dashboard_widget' => $normal_dashboard['photocrati_admin_dashboard_widget']);
				unset($normal_dashboard['photocrati_admin_dashboard_widget']);
				
				$sorted_dashboard = array_merge($widget_backup, $normal_dashboard);
				$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
			}
    }
    
    function dashboard_widget()
    {
    	$product_list = $this->_get_registry()->get_product_list();
    	$product_count = count($product_list);
    	$update_list = $this->_get_update_list();
    	$out = null;
    	
    	if ($product_count > 0)
    	{
    		$front_count = 0;
    		$list_out = null;
    		$msg_out = null;
    		
    		for ($i = 0, $l = 0; $i < $product_count; $i++)
    		{
    			$product = $this->_get_registry()->get_product($product_list[$i]);
    			
    			if (!$product->is_background_product())
    			{
		  			if ($l > 0)
		  			{
		  				$list_out .= ',';
		  			}
    			
    				$l++;
    				$front_count++;
		  			
		  			$list_out .= ' ' . $product->module_name . ' ' . __('version') . ' ' . $product->module_version;
		  			
		  			$msg_primary = $product->get_dashboard_message('primary');
		  			$msg_secondary = $product->get_dashboard_message('secondary');
		  			
		  			if ($msg_primary != null)
		  			{
		  				if ($msg_out != null)
		  				{
		  					$msg_out .= '<br/>';
		  				}
		  				
		  				$msg_out .= $msg_primary;
		  			}
		  			
		  			if ($msg_secondary != null)
		  			{
		  				if ($msg_out != null)
		  				{
		  					$msg_out .= '<br/>';
		  				}
		  				
		  				$msg_out .= $msg_secondary;
		  			}
    			}
    		}
    		
    		$out .= '<p><b>';
    		
    		if ($front_count > 1)
    		{
    			$out .= __('You are using the following products:');
    		}
    		else {
    			$out .= __('You are using');
    		}
    		
    		$out .= $list_out;
    		
    		$out .= '</b></p>';
    		
    		$out .= '<p>';
    		$out .= $msg_out;
    		$out .= '</p>';
    		
    		echo $out;
    	}
    	
    	if ($update_list != null)
    	{
    		echo '<p>There are updates available <a class="button-secondary" href="' . esc_url($this->get_update_page_url()) . '">Update Now</a></p>';
    	}
    }
}
new M_AutoUpdate_Admin();
