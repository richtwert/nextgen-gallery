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
		
    function define()
    {
        parent::define(
            'photocrati-auto_update',
            'Photocrati Auto Update',
            "Provides automatic updates",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function initialize()
    {
    }
    
    
    function _register_hooks()
    {
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    
    function admin_init()
    {
    	// XXX this should probably be moved to autoupdate-admin
    	if (isset($_GET['pclihs']))
    	{
    		if (!current_user_can('manage_options'))
    		{
    			wp_die('Permission Denied.');
    		}
    		else
    		{
    			$license_hash = $_GET['pclihs'];
    			$product = isset($_GET['pcprd']) ? $_GET['pcprd'] : null;
    			$license_hash = trim($license_hash);
    			$product = trim($product);
    			$hash_decoded = base64_decode($license_hash);
    			$product_decoded = base64_decode($product);
    			
    			if ($hash_decoded !== false)
    			{
    				$license_hash = $hash_decoded;
    			}
    			
    			if ($product_decoded !== false)
    			{
    				$product = $product_decoded;
    			}
    			
    			// XXX license at the moment is always global, ignore $product
    			$product = null;
    			
    			$license = $this->get_license($product);
    			$license_new = $this->install_license($license_hash);
    			
    			if ($license_new != null && isset($license_new['license-key']))
    			{
    				$license_new = $license_new['license-key'];
    				
    				if ($license == null || $license != $license_new)
    				{
    					$this->set_license($license_new, $product);
    					
    					$autoupdate_admin = $this->_get_registry()->get_module('photocrati-auto_update-admin');
    					
    					if ($autoupdate_admin != null)
    					{
    						wp_redirect($autoupdate_admin->get_update_page_url());
    					}
    				}
    			}
    			else
    			{
    				$error = null;
    				
    				if (isset($license_new['error']))
    				{
    					$error = $license_new['error'];
    				}
    				else if (is_string($license_new))
    				{
    					$error = $license_new;
    				}
    				
    				if ($error != null)
    				{
    					$error = ': ' . $error;
    				}
    				
    				$error .= '.';
    				
    				wp_die('Couldn\'t activate membership' . $error);
    			}
    		}
    	}
    }
    
    
    // Returns license key, retrieval from multiple sources
    function get_license($product = null)
    {
    	// XXX use Mixin_Component_Config?
    	$license_default = get_option('photocrati_license_default');
  		$product_list = $this->_get_registry()->get_product_list();
  		$path_list = array();
    	$license = null;
    	
    	if ($product != null) 
    	{
    		$license = get_option('photocrati_license_product_' . $product);
    		
    		if (array_search($product, $product_list) !== false)
    		{
    			$path_list[] = $this->_get_registry()->get_module_dir($product);
    		}
    	}
    	else
    	{
    		foreach ($product_list as $product)
    		{
    			$path_list[] = $this->_get_registry()->get_module_dir($product);
    		}
    	}
    	
  		foreach ($path_list as $path)
    	{
    		$path .= '/license.key';
    		$path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
    		
    		if (!file_exists($path))
    		{
    			$path = realpath($path);
    		}
    		
    		if (file_exists($path))
    		{
    			$license = file_get_contents($path);
    			
    			break;
    		}
    	}
    	
    	if ($license == null)
    	{
    		$license = $license_default;
    	}
    	
    	return $license;
    }
    
    function set_license($license, $product = null)
    {
    	if ($product != null) 
    	{
    		return update_option('photocrati_license_product_' . $product, $license);
    	}
    	else
    	{
    		return update_option('photocrati_license_default', $license);
    	}
    	
    	return false;
    }
    
    
    // Returns a product_id -> product_version associative array of all the loaded products
    function get_product_list()
    {
    	$product_list = $this->_get_registry()->get_product_list();
    	$version_list = array();
    	
    	foreach ($product_list as $product_id)
    	{
    		$product = $this->_get_registry()->get_product($product_id);
    		
    		$version_list[$product_id] = $product->module_version;
    	}
    	
    	return $version_list;
    }
    
    
    // Returns a module_id -> module_version associative array of all the loaded modules
    function get_module_list()
    {
    	$module_list = $this->_get_registry()->get_module_list();
    	$version_list = array();
    	
    	foreach ($module_list as $module_id)
    	{
    		$module = $this->_get_registry()->get_module($module_id);
    		
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
    	return $this->api_request(self::API_URL, 'cklic');
    }
    
    
    function install_license($license_hash)
    {
    	$result = $this->api_request(self::API_URL, 'inlic', array('license-hash' => $license_hash));
    	
    	return $result;
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
    
    
    function _download_package($module_info, $timeout = 300)
    {
			$tmpfname = wp_tempnam($module_info['module-id']);
			
			if (!$tmpfname)
			{
				return false;
			}

			$return = $this->api_request(
									self::API_URL, 'dlpkg', 
									array(
										'product-list' => array($module_info['product-id'] => $module_info['product-version']), 
										'module-list' => array($module_info['module-id'] => $module_info['module-version']),
										'module-package' => $module_info['module-package'],
										'http-timeout' => $timeout, 'http-stream' => true, 'http-filename' => $tmpfname
									));
			
			if ($return === false)
			{
				unlink($tmpfname);
				
				return false;
			}
			
			return $tmpfname;
    }
    
    // Install Module package retrieved previously (through _download_package())
    function _install_package($module_info, $package_file)
    {
    	$local_path = isset($module_info['module-local-path']) ? $module_info['module-local-path'] : null;
    	$install_path = null;
    	$current_path = $this->_get_registry()->get_module_dir($module_info['module-id']);
    	$basename = basename($current_path);
    	
    	if ($local_path != null && ($current_path == null || isset($local_path['force'])))
    	{
    		// XXX transform local relative path to absolute path
    		if (isset($local_path['product']))
    		{
    			$path = $this->_get_registry()->get_product_module_path($local_path['product']);
    			
    			if ($path != null)
    			{
						if (isset($local_path['path']))
						{
							$install_path = $path . DIRECTORY_SEPARATOR . $local_path['path'];
						}
						else
						{
							if ($basename != null)
							{
								$install_path = $path . DIRECTORY_SEPARATOR . $basename;
							}
							else
							{
								$install_path = $path . DIRECTORY_SEPARATOR . $module_info['module-id'];
							}
						}
    			}
    		}
    	}
    	else
    	{
	    	$install_path = $current_path;
    	}
    	
    	if ($install_path == null)
    	{
    		// XXX pick better default install path?
    		$path = $this->_get_registry()->get_default_module_path();
    		
    		if ($path != null)
    		{
    			$install_path = $path . DIRECTORY_SEPARATOR . $module_info['module-id'];
    		}
    	}
    	
    	if ($install_path != null && $package_file != null && is_file($package_file))
    	{
				global $wp_filesystem;
  			
    		$dir = dirname($install_path);
    		$base = basename($install_path);
    		$install_path = $dir . DIRECTORY_SEPARATOR . '__' . $base;
    		
    		$ret = unzip_file($package_file, $install_path);
    		
		  	if ($wp_filesystem != null)
		  	{
		  		$wp_filesystem->delete($package_file);
    		}
    		
    		if ($ret && !is_wp_error($ret))
    		{
    			return $install_path;
    		}
    	}
    	
    	return false;
    }
    
    
    // XXX not used...
    function _move_path_overwrite($source_path, $targe_path, $wp_filesystem)
    {
			$file_list = $wp_filesystem->dirlist($source_path, true);
			
			if ($file_list !== false)
			{
				$basedir = WP_CONTENT_DIR;
				
				foreach ($file_list as $file_path => $file_info)
				{
					if (isset($file_info['files']))
					{
						$child_list = $file_info['files'];
						
						if ($child_list == null)
						{
							
						}
					}
					
					$full_path = $source_path . $file_path;
				}
			}
    }
    
    
    // Activates previously installed Module package (through _install_package())
    function _activate_module($module_info, $install_path)
    {
			global $wp_filesystem;
  			
    	if ($wp_filesystem != null && $install_path != null && is_dir($install_path))
    	{
    		$old_prefix = '__old__';
    		$new_prefix = '__';
    		$dir = dirname($install_path);
    		$base = basename($install_path);
    		$activate_path = null;
    		$other_path = null;
    		
    		if (strpos($base, $old_prefix) === 0)
    		{
    			$activate_path = $dir . DIRECTORY_SEPARATOR . substr($base, strlen($old_prefix));
    			$other_path = $dir . DIRECTORY_SEPARATOR . $new_prefix . substr($base, strlen($old_prefix));
    		}
    		else if (strpos($base, $new_prefix) === 0)
    		{
    			$activate_path = $dir . DIRECTORY_SEPARATOR . substr($base, strlen($new_prefix));
    			$other_path = $dir . DIRECTORY_SEPARATOR . $old_prefix . substr($base, strlen($new_prefix));
    		}
    		
    		if ($activate_path != null)
    		{
    			// XXX __old__ is not used yet, needs fixing and completion
    			if ($other_path != null && is_dir($other_path))
    			{
    				$wp_filesystem->delete($other_path, true);
    			}
    			
    			if (is_dir($activate_path))
    			{
    				// XXX for now overwrite and don't replace
    				//$wp_filesystem->move($activate_path, $other_path);
    			}
    			else
    			{
  					$wp_filesystem->mkdir($activate_path);
    			}
  				
  				// XXX for overwrite to work we have to do it "manually", $wp_filesystem->move() is NEVER recursive
					$ret = copy_dir($install_path, $activate_path);
					
					if (!is_wp_error($ret))
					{
  					return $activate_path;
					}
    		}
    	}
    	
    	return false;
    }
    
    
    // Cleans up after a command is run
    // XXX $other_path is never passed in (is null) as it's not well supported by the system yet
    function cleanup_command($install_path, $activate_path, $other_path, $command_info)
    {
			global $wp_filesystem;
  			
    	if ($wp_filesystem != null)
    	{
    		// Only clean up if activation was successful
    		if ($wp_filesystem->is_dir($activate_path))
    		{
		  		if ($wp_filesystem->is_dir($install_path))
		  		{
    				$wp_filesystem->delete($install_path, true);
		  		}
		  		
		  		if ($wp_filesystem->is_dir($other_path))
		  		{
    				$wp_filesystem->delete($other_path, true);
		  		}
    		}
    	}
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
    	
    	if (!isset($parameter_list['authority-site']))
    	{
  			$authority_site = admin_url();
    		$parameter_list['authority-site'] = $authority_site;
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
  			 	// XXX FIXME 
  				//if (wp_remote_retrieve_response_code($return) == 200)
  				{
  					return true;
  				}
  			}
  			else
  			{
					$return = wp_remote_retrieve_body($return);
					$return = json_decode($return, true);
					
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
  				unset($command_info['-command-error']);
  				
  				switch ($stage)
  				{
  					case 'download':
  					{
							switch ($action)
							{
								case 'add':
								case 'update':
								{
									$package_file = $this->_download_package($command_info);
									
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
  					case 'activate':
  					case 'cleanup':
  					{
							switch ($action)
							{
								case 'add':
								case 'update':
								{
									ob_start();
									
									$old_err = error_reporting(0);
									
									$url = isset($command_info['-command-url']) ? $command_info['-command-url'] : 'admin.php';
									$url = wp_nonce_url($url);
									$creds = request_filesystem_credentials($url, '', false, false, array());
									
									$form = ob_get_clean();
									
									error_reporting($old_err);
									
									if ($creds && WP_Filesystem($creds))
									{
										unset($command_info['-command-form']);
									
										$new_stage = null;
										$new_path = null;
										
										if ($stage == 'install')
										{
											$new_stage = 'activate';
											$new_path = $this->_install_package($command_info, $command_info['-command-package-file']);
										}
										else if ($stage == 'activate')
										{
											$new_stage = 'cleanup';
											$new_path = $this->_activate_module($command_info, $command_info['-command-install-path']);
										}
										else if ($stage == 'cleanup')
										{
											$new_stage = 'none';
										
											$install_path = $command_info['-command-install-path'];
											$activate_path = $command_info['-command-activate-path'];
											
											// XXX $other_path is passed in null as it's not well supported yet
											$this->cleanup_command($install_path, $activate_path, null, $command_info);
										}
										
										if ($new_path != null || $stage == 'cleanup')
										{
											$command_info['-command-stage'] = $new_stage;
											
											if ($new_path != null)
											{
												$command_info['-command-' . $stage . '-path'] = $new_path;
											}
										}
										else
										{
											$command_info['-command-error'] = __('Could not ' . $stage . ' package.');
											$command_info['-command-stage'] = 'cleanup';
										}
									}
									else
									{
										$command_info['-command-error'] = __('No permission to ' . $stage .' package.');
										$command_info['-command-form'] = $form;
										$command_info['-command-stage'] = $stage;
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
