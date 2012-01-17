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
  			
  			// XXX testing... remove this later
  			if (is_array($return))
  			{
  				foreach ($return as $command)
  				{
  					$this->execute_api_command($command['action'], $command['info']);
  				}
  			}
  		}
    	
    	return $return;
    }
    
    
    function download_package($module_info, $timeout = 300)
    {
			$tmpfname = wp_tempnam($module_info['id']);
			
			if (!$tmpfname)
			{
				return false;
			}

			$return = $this->api_request(
									self::API_URL, 'dlpkg', 
									array(
										'product-list' => array($module_info['product'] => $module_info['product-version']), 
										'module-list' => array($module_info['id'] => $module_info['version']),
										'module-package' => $module_info['package'],
										'http-timeout' => $timeout, 'http-stream' => true, 'http-filename' => $tmpfname
									));
									
			if ($return === false)
			{
				unlink($tmpfname);
				
				return false;
			}
			
			return $tmpfname;
    }
    
    
    function install_package($module_info, $package_file)
    {
    	$install_path = isset($module_info['local-path']) ? $module_info['local-path'] : null;
    	
    	if ($install_path != null)
    	{
    		// XXX transform local relative path to absolute path
    	}
    	else
    	{
	    	$install_path = $this->_registry->get_module_dir($module_info['id']);
    	}
    	
    	if ($install_path != null && $package_file != null && is_file($package_file))
    	{
    		$dir = dirname($install_path);
    		$base = basename($install_path);
    		$install_path = $dir . DIRECTORY_SEPARATOR . '__' . $base;
    		
    		$ret = unzip_file($package_file, $install_path);
    		
    		if ($ret && !is_wp_error($ret))
    		{
    			return $install_path;
    		}
    	}
    	
    	return false;
    }
    
    
    function api_request($url, $action, $parameter_list = null)
    {
    	$url = $url . '?post_back=1&api_act=' . $action;
    	$http_args = array();
    	
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
    	
  		if (isset($parameter_list['http-timeout']))
  		{
    		$http_args['timeout'] = $parameter_list['http-timeout'];
  			
  			unset($parameter_list['http-timeout']);
  		}
    	
  		if (isset($parameter_list['http-stream']))
  		{
    		$http_args['stream'] = $parameter_list['http-stream'];
  			
  			unset($parameter_list['http-stream']);
  		}
    	
  		if (isset($parameter_list['http-filename']))
  		{
    		$http_args['filename'] = $parameter_list['http-filename'];
  			
  			unset($parameter_list['http-filename']);
  		}
    		
    	$http_args['body'] = $parameter_list;
    	$return = wp_remote_post($url, $http_args);
    	
  		if ($return != null && !is_wp_error($return))
  		{
  			if (isset($http_args['filename']))
  			{
  				if (wp_remote_retrieve_response_code($return) == 200)
  				{
  					return true;
  				}
  			}
  			else
  			{
					$return = wp_remote_retrieve_body($return);
					
					//echo $return;
					
					$return = json_decode($return, true);
					
					//var_dump($return);
					
					return $return;
  			}
  		}
  		
  		return false;
    }
    
    // Executes the required $stage for this API command and returns the new stage in the execution pipeline for the command
    function execute_api_command($api_command, $command_info, $stage = null)
    {
    	list($group, $action) = explode('-', $api_command);
    	
    	if ($stage == null)
    	{
		  	if (isset($command_info['-command-stage']))
		  	{
		  		$stage = $command_info['-command-stage'];
		  	}
		  	else
		  	{
    			$stage = 'download';
		  	}
    	}
    	
  		switch ($group)
  		{
  			case 'module':
  			{
  				switch ($stage)
  				{
  					case 'download':
  					{
							switch ($action)
							{
								case 'add':
								case 'update':
								{
									$package_file = $this->download_package($command_info);
									
									if ($package_file)
									{
										$command_info['-command-package-file'] = $package_file;
										$command_info['-command-stage'] = 'install';
									}
									else
									{
										$command_info['-command-error'] = __('Could not download package file.');
										$command_info['-command-stage'] = 'cleanup';
									}
							
									return $command_info;
								}
							}
							
							break;
  					}
  					case 'install':
  					{
							switch ($action)
							{
								case 'add':
								case 'update':
								{
									ob_start();
									
									$url = isset($command_info['-command-url']) ? $command_info['-command-url'] : 'admin.php';
									$url = wp_nonce_url($url);
									$creds = request_filesystem_credentials($url, '', false, false, array());
									
									$form = ob_get_clean();
									
									if ($creds && WP_Filesystem($creds))
									{
										$install_path = $this->install_package($command_info, $command_info['-command-package-file']);
										
										if ($install_path)
										{
											$command_info['-command-stage'] = 'activate';
											$command_info['-command-install-path'] = $install_path;
										}
										else
										{
											$command_info['-command-error'] = __('Could not install package.');
											$command_info['-command-stage'] = 'cleanup';
										}
									}
									else
									{
										$command_info['-command-form'] = $form;
										$command_info['-command-stage'] = 'install';
									}
							
									return $command_info;
								}
							}
							
							break;
  					}
  				}
					
					break;
  			}
			}
			
			return null;
    }
}
new M_AutoUpdate();
