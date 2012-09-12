<?php

class C_Dynamic_Thumbnails_Controller extends C_MVC_Controller
{
	function define($context=FALSE)
	{
		parent::define($context);
	}

	function index()
	{
		$uri = $_SERVER['REQUEST_URI'];
		$regex = '/^\\/nextgen_image\\/(\\d+)(?:\\/(.*))?/';
		$match = null;
		
		if (preg_match($regex, $uri, $match) > 0)
		{
			$image_id = $match[1];
			$uri_args = isset($match[2]) ? explode('/', $match[2]) : array();
			$params = array(
				'width' => null,
				'height' => null,
				'quality' => null,
				'crop' => null,
				'watermark' => null,
				'reflection' => null,
			);
			
			foreach ($uri_args as $uri_arg)
			{
				$size_match = null;
				
				if ($uri_arg == 'watermark')
				{
					$params['watermark'] = true;
				}
				else if ($uri_arg == 'reflection')
				{
					$params['reflection'] = true;
				}
				else if ($uri_arg == 'crop')
				{
					$params['crop'] = true;
				}
				else if (preg_match('/(\\d+)x(\\d+)(?:x(\\d+))?/i', $uri_arg, $size_match) > 0)
				{
					$params['width'] = $size_match[1];
					$params['height'] = $size_match[2];
					$params['quality'] = isset($size_match[3]) ? $size_match[3] : null;
				}
			}
			
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
