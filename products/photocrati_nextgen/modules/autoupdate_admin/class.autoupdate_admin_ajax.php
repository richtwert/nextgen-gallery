<?php

class Mixin_AutoUpdate_Admin_Ajax extends Mixin
{
	function _handle_action($action, $params)
	{
		if (!current_user_can('update_plugins'))
		{
			// Not allowed, skip...
			return null;
		}
		
		$updater = $this->_get_registry()->get_module('photocrati-auto_update');
		
		if ($updater != null)
		{
			$result = null;
			
			switch ($action)
			{
				case 'handle-item':
				{
					$item = $params['update-item'];
					$command_action = $item['action'];
					$command_info = $item['info'];
					$command_stage = isset($command_info['-command-stage']) ? $command_info['-command-stage'] : null;
					
					// XXX this is just to load a nice icon...but seems to be broken ('index' loads 'dashboard' which is missing)
					if ($command_stage == 'install')
					{
						$layout_screen = null;
			
						if (function_exists('get_current_screen'))
						{
							$layout_screen = get_current_screen();
						}
						else
						{
							global $current_screen;
					
							$layout_screen = $current_screen;
						}
				
						if ($layout_screen == null && function_exists('set_current_screen'))
						{
							set_current_screen('index');
						}
					}
			
					$result = $updater->execute_api_command($command_action, $command_info);
					
					return array('action' => $command_action, 'info' => $result);
				}
				case 'handle-list':
				{
					$item_list = $params['update-list'];
					$return_list = array();
					
					foreach ($item_list as $item)
					{
						$command_action = $item['action'];
						$command_info = $item['info'];
						$command_stage = isset($command_info['-command-stage']) ? $command_info['-command-stage'] : null;
						
						// Atomic handling of entire command lists is only supported for activation stage
						if ($command_stage == 'activate')
						{
							$result = $updater->execute_api_command($command_action, $command_info);
							
							$item['info'] = $result;
						}
						
						$return_list[] = $item;
					}
					
					return $return_list;
				}
				default:
				{
					return null;
				}
			}
		}
		
		return null;
	}
	
	function handle_ajax()
	{
		check_ajax_referer('pc-autoupdate-admin-nonce', 'actionSec');

		if (!isset($_POST['update-action']) || $_POST['update-action'] == null) {
			return;
		}
		
		$action = $_POST['update-action'];
		$params = $_POST;
		
		$response = $this->_handle_action($action, $params);

		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		
		if ($response != null) {
			$response = json_encode($response);

			header('Content-Type: application/json');

			echo $response;
		}
		else {
			header('HTTP/1.1 403 Forbidden');
		}

		exit();
	}
}

class C_AutoUpdate_Admin_Ajax extends C_Component
{
	function define()
	{
		parent::define();
		
		$this->add_mixin('Mixin_AutoUpdate_Admin_Ajax');
	}

	function initialize($context = FALSE)
	{
		parent::initialize($context);
	}
}
