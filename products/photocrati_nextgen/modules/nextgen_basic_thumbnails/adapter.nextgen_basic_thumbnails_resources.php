<?php

class A_NextGen_Basic_Thumbnails_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueues resources required for NextGEN thumbnails settings management',
			__CLASS__,
			'enqueue_nextgen_basic_thumbnails_resources'
		);
	}

	function enqueue_nextgen_basic_thumbnails_resources()
	{
		  wp_enqueue_style(
		      'ngg_basic_thumbnails_settings',
		      $this->static_url('nextgen_basic_thumbnails_settings.css'),
		      false,
		      $this->module_version,
		      TRUE
		  );
		  
		  wp_enqueue_script(
		      'ngg_basic_thumbnails_settings',
		      $this->static_url('nextgen_basic_thumbnails_settings.js'),
		      array('jquery'),
		      $this->module_version,
		      TRUE
		  );
	}
}
