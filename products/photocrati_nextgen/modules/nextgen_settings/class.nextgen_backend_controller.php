<?php

class C_NextGen_Backend_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Backend_Controller');
		$this->add_global_pre_hook(
			'Enqueue Backend Resources',
			'Hook_Enqueue_Backend_Resources',
			'enqueue_backend_resources'
		);
		$this->implement('I_NextGen_Backend_Controller');
	}

	/**
	 * Gets an instance of the controller
	 * @param string $context
	 * @return C_NextGen_Settings_Controller
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = function_exists('get_called_class') ?
				get_called_class() : get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provides the default implementation for a NextGEN Admin Controller
 */
class Mixin_NextGen_Backend_Controller extends Mixin
{
	function enqueue_backend_resources()
	{
		wp_enqueue_script(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.js')
		);
		wp_enqueue_style(
			'nextgen_admin_settings',
			$this->static_url('nextgen_admin_settings.css')
		);
	}
}


/**
 * Enqueues backend resources whenever an MVC Controller action is executed
 */
class Hook_Enqueue_Backend_Resources extends Hook
{
	function enqueue_backend_resources()
	{
		if (preg_match("/_action$/", $this->method_called)) {
			$this->object->enqueue_backend_resources();
		}
	}
}