<?php

class C_Dynamic_Thumbnails_Controller extends C_MVC_Controller
{
	function define($context=FALSE)
	{
		parent::define($context);
		
		$this->add_mixin('Mixin_Dynamic_Thumnbails_Manager');
	}

	function index_action()
	{
		$uri = $_SERVER['REQUEST_URI'];
		$params = $this->object->get_params_from_uri();
		
		if ($params != null)
		{
			$image_id = $params['image'];
			$storage = $this->get_registry()->get_utility('I_Gallery_Storage');
			
			$thumbnail = $storage->generate_thumbnail(
				$image_id, 
				$params['width'], $params['height'], 
				$params['crop'], $params['quality'], 
				$params['watermark'], $params['reflection'],
				true
			);
			
			if ($thumbnail) {
				// output image and headers
				$thumbnail->show();
			}
		}
	}
}
