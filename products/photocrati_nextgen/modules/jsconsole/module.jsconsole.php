<?php
/*
{
	Module: photocrati-jsconsole
}
 */

class M_JsConsole extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'photocrati-jsconsole',
			'JS Console',
			'Provides remote debugging capbilities utilizing jsconsole.com',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
			$context
		);
	}

	function _register_adapters()
	{
		// Add settings to "Other Options" page
		$this->get_registry()->add_adapter(
			'I_NextGen_Settings_Controller',
			'A_JsConsole_Controller'
		);
	}

	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_jsconsole'));
	}


	function enqueue_jsconsole()
	{
		$settings = $this->get_registry()->get_utility('I_NextGen_Settings');
		if ($settings->jsconsole_enabled && $settings->jsconsole_session_key) {
			wp_register_script(
				'jsconsole-remote',
				'http://jsconsole.com/remote.js?'.$settings->jsconsole_session_key,
				NULL,
				NULL
			);
			wp_enqueue_script('jsconsole-remote');
		}
	}
}

new M_JsConsole();