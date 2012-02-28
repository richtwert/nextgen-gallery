<?php

class A_AutoUpdate_Admin_Ajax extends Mixin
{
	function autoupdate_admin_handle()
	{
		if (!current_user_can('update_plugins'))
		{
			// Not allowed, skip...
			return null;
		}
		
		// XXX ugly, one of the reasons to use ajax-admin.php
		include_once(ABSPATH . '/wp-admin/includes/admin.php');
		
		$updater = $this->_registry->get_module('photocrati-auto_update');
		$action = $this->param('update-action');
		
		if ($updater != null)
		{
			$result = null;
			
			switch ($action)
			{
				case 'handle-item':
				{
					$item = $this->param('update-item');
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
					$item_list = $this->param('update-list');
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
}
