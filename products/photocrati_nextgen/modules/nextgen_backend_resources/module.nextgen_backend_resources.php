<?php

/***
	{
		Module: photocrati-nextgen_backend_resources
	}
***/
class M_NextGen_Backend_Resources extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'photocrati-nextgen_backend_resources',
			'NextGEN Admin',
			'Provides a component for centrally managing static resources required by NextGEN in the backend',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
			$context
		);
		$this->object->add_mixin('Mixin_MVC_Controller_Rendering');
	}

	/**
	 * Register hooks required for the WordPress framework
	 */
	function _register_hooks()
	{
		add_action('admin_init', array(&$this, 'enqueue_resources'), 1);
	}


	/**
	 * Enqueues static resources needed for the NextGen Admin interface
	 */
	function enqueue_resources()
	{
		wp_enqueue_script(
			'nextgen_admin_settings', $this->static_url('nextgen_admin_settings.js')
		);
		wp_enqueue_style(
			'nextgen_admin_settings', $this->static_url('nextgen_admin_settings.css')
		);
	}
}

new M_NextGen_Admin();