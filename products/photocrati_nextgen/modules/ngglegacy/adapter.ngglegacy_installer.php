<?php

class A_NggLegacy_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'uninstall',
			get_class(),
			get_class(),
			'uninstall_ngglegacy'
		);
	}

	function uninstall_ngglegacy()
	{
		delete_option('ngg_init_check');
        delete_option('ngg_update_exists');
	}
}