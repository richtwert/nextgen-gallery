<?php

class A_Thumbnail_Dimension_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueues resources required for the thumbnail dimensions widget',
			__CLASS__,
			'enqueue_resources_for_thumbnail_dimensions_widget'
		);
	}

	function enqueue_resources_for_thumbnail_dimensions_widget()
	{
		wp_enqueue_script(
			'ngg_thumbnail_dimensions',
			$this->static_url('ngg_thumbnail_dimensions.js'),
			array('jquery')
		);
	}
}