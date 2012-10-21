<?php

/***
    {
        Module: photocrati-frame_communication
    }
***/

class M_Frame_Communication extends C_Base_Module
{
	function define($context=FALSE)
	{
		$this->add_mixin('Mixin_MVC_Controller_Rendering');
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

	function initialize()
	{
		$publisher = $this->get_registry()->get_utility('I_Frame_Event_Publisher', 'attach_to_post');
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
		wp_register_script(
			'frame_event_publisher',
			$this->static_url('frame_event_publisher.js'),
			array('jquery')
		);
		wp_enqueue_script('frame_event_publisher');
	}
}

new M_Frame_Communication();