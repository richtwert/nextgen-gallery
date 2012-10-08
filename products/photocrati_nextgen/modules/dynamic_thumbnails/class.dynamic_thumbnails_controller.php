<?php

class C_Dynamic_Thumbnails_Controller extends C_MVC_Controller
{
	function define($context=FALSE)
	{
		parent::define($context);
	}

	function index_action()
	{
		$dynthumbs = $this->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
		
		$uri = $_SERVER['REQUEST_URI'];
		$params = $dynthumbs->get_params_from_uri($uri);
		
		if ($params != null)
		{
			$storage = $this->get_registry()->get_utility('I_Gallery_Storage');
			
			// Note, URLs should always include quality setting when returned by Gallery Storage component
			// this sanity check is mostly for manually testing URLs
			if (!isset($params['quality'])) {
				$params['quality'] = 100;
			}
			
			$image_id = $params['image'];
			$size = $dynthumbs->get_size_name($params);
			
			$storage->render_image($image_id, $size);
		}
	}
}
