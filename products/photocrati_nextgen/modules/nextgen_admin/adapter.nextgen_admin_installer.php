<?php

class A_NextGen_Admin_Installer extends Mixin
{
	var $capabilities = array(
		'ngg_manage_display_settings'
	);

	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_nextgen_admin_module'
		);

		$this->object->add_post_hook(
			'uninstall',
			get_class().'::Uninstall',
			get_class(),
			'uninstall_nextgen_admin_module'
		);
	}

	function get_current_actor()
	{
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_actor = $security->get_current_actor();
		return $sec_actor;
	}

	function install_nextgen_admin_module()
	{
		foreach ($this->capabilities as $cap) {
			$this->get_current_actor()->add_capability($cap);
		}
	}

	function uninstall_nextgen_admin_module()
	{
		foreach ($this->capabilities as $cap) {
			$this->get_current_actor()->remove_capability($cap);
		}
	}
}