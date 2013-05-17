<?php
/*
{
	Module: photocrati-installer,
	Depends: { photocrati-settings }
}
 */
class M_Installer extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-installer',
			'Installer',
			'Provides an installer for modules to use',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Installer', 'C_Module_Installer');
	}
	
	function _register_hooks()
	{
		add_action('init', array($this, 'init_wp'), 99);
	}
	
	function init_wp()
	{
		$this->get_registry()->get_utility('I_Installer')->perform_automatic_install();
	}

  function get_type_list()
  {
      return array(
          'C_Module_Installer' => 'class.module_installer.php',
          'I_Installer' => 'interface.installer.php'
      );
  }
}

new M_Installer;
