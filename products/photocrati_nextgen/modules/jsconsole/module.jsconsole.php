<?php
/*
{
	Module: photocrati-jsconsole,
	Depends: { photocrati-mvc }
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

		$this->get_registry()->add_adapter(
			'I_Settings_Manager',
			'A_JsConsole_Settings', $this->module_id
		);
	}

	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_jsconsole'));
	}


	function enqueue_jsconsole()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager')->group('photocrati-jsconsole');
		if ($settings->jsconsole_enabled && $settings->jsconsole_session_key && !is_admin()) {
			wp_register_script(
				'jsconsole-remote',
				'http://jsconsole.com/remote.js?'.$settings->jsconsole_session_key,
				NULL,
				NULL
			);
			wp_enqueue_script('jsconsole-remote');
		}
	}

    function get_type_list()
    {
        return array(
            'A_Jsconsole_Controller' => 'adapter.jsconsole_controller.php',
            'A_Jsconsole_Settings' => 'adapter.jsconsole_settings.php'
        );
    }
}

new M_JsConsole();
