<?php

class A_Attach_To_Post_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueue static resources required for the Attach to Post interface',
			__CLASS__,
			'enqueue_attach_to_post_resources'
		);
	}

	function enqueue_attach_to_post_resources()
	{
		// Enqueue JQuery UI libraries
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');

		// Enqueue chosen, a library to make our drop-downs look pretty
		wp_enqueue_style('chosen', $this->static_url('chosen.css'));
		wp_enqueue_script(
			'chosen', $this->static_url('chosen.js'), array('jquery')
		);

		// Ensure we have the AJAX module ready
		wp_enqueue_script('photocrati_ajax', PHOTOCRATI_GALLERY_AJAX_URL.'/js');

		// Enqueue logic for the Attach to Post interface as a whole
		wp_enqueue_script(
			'ngg_attach_to_post', $this->static_url('attach_to_post.js')
		);
		wp_enqueue_style(
			'ngg_attach_to_post', $this->static_url('attach_to_post.css')
		);

		// Enqueue our Ember.js application for the "Display Tab"
		wp_enqueue_script(
			'handlebars',
			$this->static_url('handlebars-1.0.0.beta.6.js')
		);
		wp_enqueue_script(
			'ember',
			$this->static_url('ember-1.0.pre.js'),
			array('jquery', 'handlebars')
		);
	}
}