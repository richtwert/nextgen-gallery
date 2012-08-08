<?php

class C_Ajax_Controller extends C_MVC_Controller
{
	function define()
	{
		parent::define();
		$this->implement('I_Ajax_Controller');
	}

	function index()
	{
		$retval = FALSE;

		// Inform the MVC framework what type of content we're returning
		$this->set_content_type('json');

		// Get the action requested & find and execute the related method
		if (($action = $this->param('action'))) {
			$method = "{$action}_action";
			if ($this->has_method($method)) {
				$retval = $this->call_method($method);
			}
		}

		// If no retval has been set, then return an
		// error
		if (!$retval) {
			$retval = array('error' => 'Not a valid AJAX action');
		}

		// Return the JSON to the browser
		echo json_encode($retval);
	}


	function js()
	{
		$this->set_content_type('javascript');
		$this->render_partial('ajax_js', array(
			'ajax_url'	=>	PHOTOCRATI_GALLERY_AJAX_URL
		));
	}
}