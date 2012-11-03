<?php

class A_NextGen_Basic_Template_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueues resources required for NextGEN template widget',
			__CLASS__,
			'enqueue_nextgen_basic_template_resources'
		);
	}

	function enqueue_nextgen_basic_template_resources()
	{
        wp_enqueue_style('ngg_template_settings', $this->static_url('ngg_template_settings.css'));
        wp_enqueue_script(
            'ngg_template_settings',
            $this->static_url('ngg_template_settings.js'),
            array('jquery-ui-autocomplete', 'jquery-ui-button'),
            $this->module_version,
            TRUE
        );
    }
}
