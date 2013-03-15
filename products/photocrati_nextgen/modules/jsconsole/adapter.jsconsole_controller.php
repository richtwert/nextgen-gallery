<?php

class A_JsConsole_Controller extends Mixin
{
	function initialize()
	{
		// Add a hook to add the jsconsole.com tab
		$this->object->add_post_hook(
			'_get_tabs',
			'Add tab for JsConsole',
			get_class(),
			'_add_jsconsole_tab'
		);

		// Add static resources for the jsconsole.com tab
		$this->object->add_post_hook(
			'enqueue_backend_resources',
			'Enqueue jsconsole.com tab static resources',
			get_class(),
			'_enqueue_jsconsole_settings_resources'
		);
	}

	/**
	 * Static resources needed for the jsconsole tab
	 */
	function _enqueue_jsconsole_settings_resources()
	{
		wp_register_script('jsconsole_settings', $this->object->get_static_url('jsconsole#jsconsole_settings.js'), array('jquery'));
		wp_enqueue_script('jsconsole_settings');
	}


	/**
	 * Add the jsconsole tab to the NextGen Settings page
	 * @param array $settings
	 */
	function _add_jsconsole_tab($settings)
	{
		// Get the current list of tabs
		$tabs = $this->object->get_method_property(
			$this->method_called,
			ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
			array()
		);

		// Add the tab
		$tabs[_('JS Console (jsconsole.com)')] = $this->object->_render_jsconsole_tab($settings);

		// Set as the return value
		$this->object->set_method_property(
			$this->method_called,
			ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
			$tabs
		);

		return $tabs;
	}

	/**
	 * Renders the jsconsole tab
	 * @param array $settings
	 */
	function _render_jsconsole_tab($settings)
	{
		return $this->object->render_partial('jsconsole#jsconsole_settings', array(
			'jsconsole_enabled_label'		=>	'Enable jsconsole.com support?',
			'jsconsole_enabled_tooltip'		=>	'Provides remote debugging capabilities utilizing jsconsole.com',
			'jsconsole_enabled'				=>	$settings['jsconsole_enabled'],
			'jsconsole_session_key_label'	=>	'Session key',
			'jsconsole_session_key_tooltip'	=>	'Name of the session that jsconsole.com is listening for',
			'jsconsole_session_key'			=>	$settings->get(
				'jsconsole_session_key',
				$this->object->_generate_jsconsole_session_key()
			)
		), TRUE);
	}


	function _generate_jsconsole_session_key()
	{
		$site_url = $this->object->get_registry()->get_utility('I_Router')->get_base_url();
		return str_replace(
			array('http://', 'https://', '/', '.'),
			array('', '', '-', '-'),
			$site_url
		);
	}
}