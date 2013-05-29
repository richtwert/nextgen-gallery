<?php

class Mixin_Settings_Installer extends Mixin
{
	function _get_product_install_list()
	{
		$products = $this->get_registry()->get_product_list();
		$list = array();
		
		foreach ($products as $product_id)
		{
			$product = $this->get_registry()->get_product($product_id);
			$version = $product->module_version;
			
			$list[$product_id] = array('latest-version' => $version);
		}
		
		return $list;
	}
	
	function perform_automatic_install()
	{
		$list = $this->object->_get_product_install_list();
		$list_old = $this->object->settings->product_install_list;
		$run_install = false;
		$run_uninstall = false;
		
		if ($list_old == null && $list != null)
		{
			$run_install = true;
		}
		
		foreach ($list as $product_id => $product_info)
		{
			if (!isset($list_old[$product_id]))
			{
				$run_install = true;
				
				continue;
			}
			
			$product_info_old = $list_old[$product_id];
			$version_old = $product_info_old['latest-version'];
			$version = $product_info['latest-version'];
			
			if (version_compare($version_old, $version, '<'))
			{
				$run_uninstall = true;
				$run_install = true;
			}
			
			unset($list_old[$product_id]);
		}
		
		if (count($list_old) > 0)
		{
			$run_uninstall = true;
		}
		
		if ($run_uninstall)
		{
			$this->object->uninstall(NEXTGEN_GALLERY_PLUGIN_BASENAME);
		}
		
		if ($run_install)
		{
			$this->object->install(NEXTGEN_GALLERY_PLUGIN_BASENAME);
		}
		
		return $run_install;
	}
	
	function install($product)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$list = $this->object->_get_product_install_list();
		$this->object->settings->product_install_list = $list;
		$this->object->global_settings->save();
		$this->object->settings->save();
	}

	function uninstall($product, $hard = FALSE)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->global_settings->destroy();
		$this->object->settings->destroy();
	}
}

class C_Module_Installer extends C_Component
{
	static $_instances = array();
	var $global_settings;
	var $settings;

	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Settings_Installer');
		$this->implement('I_Installer');
	}

	function initialize()
	{
		parent::initialize();
		$this->global_settings	= $this->get_registry()->get_utility(
			'I_Settings_Manager',
			'global'
		);

		$this->settings			= $this->get_registry()->get_utility(
			'I_Settings_Manager'
		);
	}
}
