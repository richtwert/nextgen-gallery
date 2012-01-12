<?php

/***
	{
		Module: photocrati-auto_update
	}
***/

class M_AutoUpdate extends C_Base_Module
{
    // XXX change URL
    const API_URL = 'http://members.photocrati.com/api/';
		
    function initialize()
    {
        parent::initialize(
            'photocrati-auto_update',
            'Photocrati Auto Update',
            "Provides automatic updates",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function _register_hooks()
    {
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    
    function admin_init()
    {
    	$this->check_license();
    	//$this->check_updates();
    	
    	$this->check_product_list();
    }
    
    
    // Returns license key, retrieval from multiple sources
    function get_license()
    {
    	// XXX use Mixin_Component_Config?
    	$license = get_option('photocrati_gallery_plugin_license');
    	
    	if ($license == null)
    	{
    		$path = dirname(__FILE__) . '/../../license.key';
    		$path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
    		
    		if (!file_exists($path))
    		{
    			$path = realpath($path);
    		}
    		
    		if (file_exists($path))
    		{
    			$license = file_get_contents($path);
    		}
    	}
    	
    	return $license ? $license : null;
    }
    
    
    // Returns a product_id -> product_version associative array of all the loaded products
    function get_product_list()
    {
    	$product_list = $this->_registry->get_product_list();
    	$version_list = array();
    	
    	foreach ($product_list as $product_id)
    	{
    		$product = $this->_registry->get_product($product_id);
    		
    		$version_list[$product_id] = $product->module_version;
    	}
    	
    	return $version_list;
    }
    
    
    // Returns a module_id -> module_version associative array of all the loaded modules
    function get_module_list()
    {
    	$module_list = $this->_registry->get_module_list();
    	$version_list = array();
    	
    	foreach ($module_list as $module_id)
    	{
    		$module = $this->_registry->get_module($module_id);
    		
    		$version_list[$module_id] = $module->module_version;
    	}
    	
    	return $version_list;
    }
    
    
    function push_product_check($product_id, $callback = null)
    {
    	
    }
    
    
    function _product_check_callback($action, $message)
    {
    
    }
    
    
    function check_license()
    {
    	$this->api_request(self::API_URL, 'cklic');
    }
    
    
    function check_product($product_id)
    {
    	return $this->check_product_list(array($product_id));
    }
    
    
    function check_product_list($product_list = null)
    {
  		$list_whole = $this->get_product_list();
  		$list_use = array();
    	$return = array();
    	
    	if ($product_list == null)
    	{
    		$product_list = array_keys($list_whole);
    	}
  	
  		foreach ($product_list as $product_id)
  		{
  			if (isset($list_whole[$product_id]))
  			{
  				$list_use[$product_id] = $list_whole[$product_id];
  			}
  		}
  		
  		if ($list_use != null)
  		{
  			$return = $this->api_request(self::API_URL, 'ckups', array('product-list' => $list_use));
  		}
    	
    	return $return;
    }
    
    
    function update_module($module_id, $api_manifest)
    {
    	
    }
    
    
    function api_request($url, $action, $parameter_list = null)
    {
    	$url = $url . '?post_back=1&api_act=' . $action;
    	
    	if (!isset($parameter_list['license-key']))
    	{
  			$license_key = $this->get_license();
    		$parameter_list['license-key'] = $license_key;
    	}
    	
    	if (!isset($parameter_list['product-list']))
    	{
    		$product_list = $this->get_product_list();
    		$parameter_list['product-list'] = $product_list;
    	}
    	
    	if (!isset($parameter_list['module-list']))
    	{
    		$module_list = $this->get_module_list();
    		$parameter_list['module-list'] = $module_list;
    	}
    	
    	$return = wp_remote_post($url, array('body' => $parameter_list));
  		
  		if ($return != null && !is_wp_error($return))
  		{
  			$return = wp_remote_retrieve_body($return);
  			
  			//echo $return;
  			
  			$return = json_decode($return, true);
  			
  			//var_dump($return);
  			
  			return $return;
  		}
  		
  		return false;
    }
}
new M_AutoUpdate();
