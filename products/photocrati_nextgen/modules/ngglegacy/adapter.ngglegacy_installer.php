<?php

class A_NggLegacy_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_ngglegacy'
		);
		
		$this->object->add_post_hook(
			'uninstall',
			get_class(),
			get_class(),
			'uninstall_ngglegacy'
		);
	}

	function install_ngglegacy()
	{
        include_once('admin/install.php');
		nggallery_install();
	}

	function uninstall_ngglegacy()
	{
		delete_option('ngg_init_check');
        delete_option('ngg_update_exists');
	}
}
