<?php

/***
    {
        Module: photocrati-frame_communication,
		Depends: { photocrati-router, photocrati-settings }
    }
***/

class M_Frame_Communication extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'photocrati-frame_communication',
			'Frame/iFrame Inter-Communication',
			'Provides a means for HTML frames to share server-side events with each other',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
			$context
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Settings_Manager', 'A_Frame_Communication_Settings', $this->module_id
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility(
			'I_Frame_Event_Publisher', 'C_Frame_Event_Publisher'
		);
	}

	function _register_hooks()
	{
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_scripts'));

	}

	function enqueue_admin_scripts()
	{
		$router = $this->get_registry()->get_utility('I_Router');

		wp_register_script(
			'frame_event_publisher',
			$router->get_static_url('frame_communication#frame_event_publisher.js'),
			array('jquery')
		);
		wp_enqueue_script('frame_event_publisher');
	}

    function set_file_list()
    {
        return array(
            'adapter.frame_communication_settings.php',
            'class.frame_event_publisher.php',
            'interface.frame_event_publisher.php'
        );
    }
}

new M_Frame_Communication();
