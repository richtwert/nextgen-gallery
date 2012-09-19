<?php

class Mixin_Dynamic_Thumnbails_Manager extends Mixin
{
	function get_route_name()
	{
		return 'nextgen_image';
	}
	
	function _get_params_sanitized($params)
	{
		// XXX pick defaults below from NGG settings?
		if (!isset($params['quality']))
		{
			$params['quality'] = 100;
		}
		
		if (!isset($params['crop']))
		{
			$params['crop'] = true;
		}
		
		if (!isset($params['watermark']))
		{
			$params['watermark'] = true;
		}
		
		if (!isset($params['reflection']))
		{
			$params['reflection'] = false;
		}
		
		return $params;
	}
	
	function get_uri_from_params($params)
	{
		$params = $this->object->_get_params_sanitized($params);
		$image = $params['image'];
		$image_id = is_int($image) ? $image : $image->pid;
		$image_width = $params['width'];
		$image_height = $params['height'];
		$image_quality = $params['quality'];
		$image_crop = $params['crop'];
		$image_watermark = $params['watermark'];
		$image_reflection = $params['reflection'];
		
		$uri = null;
		
		$uri .= '/';
		$uri .= $this->object->get_route_name() . '/';
		$uri .= strval($image_id) . '/';
		
		$uri .= $image_width . 'x' . $image_height . 'x' . $image_quality . '/';
		
		if ($image_crop)
		{
			$uri .= 'crop/';
		}
		
		if ($image_watermark)
		{
			$uri .= 'watermark/';
		}
		
		if ($image_reflection)
		{
			$uri .= 'reflection/';
		}
		
		return $uri;
	}
	
	function get_image_uri($image, $params)
	{
		$params['image'] = $image;
		$uri = $this->object->get_uri_from_params($params);
		
		return $uri;
	}
	
	function get_image_url($image, $params)
	{
		$url = site_url() . $this->object->get_image_uri($image, $params);
		
		return $url;
	}
	
	function get_params_from_uri($uri)
	{
		$regex = '/^\\/' . $this->object->get_route_name() . '\\/(\\d+)(?:\\/(.*))?/';
		$match = null;
		
		if (preg_match($regex, $uri, $match) > 0)
		{
			$image_id = $match[1];
			$uri_args = isset($match[2]) ? explode('/', $match[2]) : array();
			$params = array(
				'image' => $image_id,
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
			
			return $params;
		}
		
		return null;
	}
	
	function _get_name_prefix_list()
	{
		return array(
			'id' => 'nggid0',
			'size' => 'ngg0dyn-',
			'flags' => '00f0',
			'flag' => array('w0' => 'watermark', 'c0' => 'crop', 'r0' => 'reflection'),
			'flag_len' => 2,
		);
	}
	
	function get_name_from_params($params, $only_size_name = false, $id_in_name = true)
	{
		$prefix_list = $this->object->_get_name_prefix_list();
		$id_prefix = $prefix_list['id'];
		$size_prefix = $prefix_list['size'];
		$flags_prefix = $prefix_list['flags'];
		$flags = $prefix_list['flag'];
		
		$params = $this->object->_get_params_sanitized($params);
		$image = isset($params['image']) ? $params['image'] : null;
		$image_width = $params['width'];
		$image_height = $params['height'];
		$image_quality = $params['quality'];
		$image_crop = $params['crop'];
		$image_watermark = $params['watermark'];
		$image_reflection = $params['reflection'];
		
		$extension = null;
		$name = null;
		
		if (!$only_size_name)
		{
			if (is_int($image))
			{
        $imap = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
        $image = $imap->find($image);
			}
			
			if ($image != null)
			{
				$extension = pathinfo($image->filename, PATHINFO_EXTENSION);
				
				if ($extension != null)
				{
					$extension = '.' . $extension;
				}
				
				$name .= basename($image->filename, $extension);
				$name .= '-';
				
				if ($id_in_name)
				{
					$image_id = strval($image->pid);
					$id_len = dechex(strlen($image_id));
					
					if (strlen($id_len) == 1)
					{
						$name .= $id_prefix . $id_len . $image_id;
						$name .= '-';
					}
				}
			}
		}
		
		$name .= $size_prefix;
		$name .= strval($image_width) . 'x' . strval($image_height) . 'x' . strval($image_quality);
		$name .= '-';
		
		$name .= $flags_prefix;
		
		foreach ($flags as $flag_prefix => $flag_name)
		{
			$flag_value = 0;
			
			if (isset($params[$flag_name]))
			{
				$flag_value = $params[$flag_name];
				
				if (!is_string($flag_value))
				{
					$flag_value = intval($flag_value);
					$flag_value = sprintf('%d', $flag_value);
				}
			}
			
			$flag_value = strval($flag_value);
			$flag_len = dechex(strlen($flag_value));
			
			if (strlen($flag_len) == 1)
			{
				$name .= $flag_prefix . $flag_len . $flag_value;
			}
		}
		
		$name .= $extension;
		
		return $name;
	}
	
	function get_size_name($params)
	{
		$name = $this->object->get_name_from_params($params, true);
		
		return $name;
	}
	
	function get_image_name($image, $params)
	{
		$params['image'] = $image;
		$name = $this->object->get_name_from_params($params);
		
		return $name;
	}
	
	function get_params_from_name($name, $is_only_size_name = false)
	{
		$prefix_list = $this->object->_get_name_prefix_list();
		$id_prefix = $prefix_list['id'];
		$size_prefix = $prefix_list['size'];
		$flags_prefix = $prefix_list['flags'];
		$size_name = null;
		$id_name = null;
		$params = array();
		
		if (!$is_only_size_name)
		{
			$extension = pathinfo($name, PATHINFO_EXTENSION);
		
			if ($extension != null)
			{
				$extension = '.' . $extension;
			}
		
			$name = basename($name, $extension);
		}
		
		$size_index = strrpos($name, $size_prefix);
		
		if ($size_index > 0 || $size_index === 0)
		{
			// check if name contains dynamic size/params info by looking for prefix
			$size_name = substr($name, $size_index);
		}
		
		if (!$is_only_size_name)
		{
			// name should contain the image id, search for prefix
			$id_index = strrpos($name, $id_prefix);
			
			if ($id_index > 0 || $id_index === 0)
			{
				if ($size_index > 0 && $size_index > $id_index)
				{
					$id_name = substr($name, $id_index, ($size_index - $id_index));
				}
				else
				{
					$id_name = substr($name, $id_index);
				}
			}
		}
		
		// Double check we got a correct dynamic size/params string
		if (substr($size_name, 0, strlen($size_prefix)) == $size_prefix)
		{
			$flags = $prefix_list['flag'];
			$flag_id_len = $prefix_list['flag_len'];
			$params_str = substr($size_name, strlen($size_prefix));
			$params_parts = explode('-', $params_str);
			
			foreach ($params_parts as $param_part)
			{
				// Parse WxHxQ - Q=quality
				$param_size = explode('x', $param_part);
				$param_size_count = count($param_size);
				
				if (substr($param_part, 0, strlen($flags_prefix)) == $flags_prefix)
				{
					// Set flags, using $flags keys as prefixes
					$param_flags = substr($param_part, strlen($flags_prefix));
					$param_flags_len = strlen($param_flags);
					$flags_todo = $flags;
					
					while (true)
					{
						if (count($flags_todo) == 0 || strlen($param_flags) == 0)
						{
							break;
						}
						
						$flag_prefix = substr($param_flags, 0, $flag_id_len);
						$param_flags = substr($param_flags, $flag_id_len);
						
						if (isset($flags[$flag_prefix]))
						{
							$flag_name = $flags[$flag_prefix];
							$flag_value_len = min(hexdec(substr($param_flags, 0, 1)), strlen($param_flags) - 1);
							$flag_value = substr($param_flags, 1, $flag_value_len);
							$param_flags = substr($param_flags, $flag_value_len + 1);
							
							if (is_numeric($flag_value))
							{
								$flag_value = intval($flag_value);
							}
							
							$params[$flag_name] = $flag_value;
							
							if (isset($flags_todo[$flag_prefix]))
							{
								unset($flags_todo[$flag_prefix]);
							}
						}
						else
						{
							// XXX unknown flag?
						}
					}
				}
				else if ($param_size_count == 2 || $param_size_count == 3)
				{
					// Set W H Q
					$params['width'] = intval($param_size[0]);
					$params['height'] = intval($param_size[1]);
					$params['quality'] = intval(isset($param_size[2]) ? $param_size[2] : 100);
				}
			}
		}
		
		// Double check we got a correct id string
		if (substr($id_name, 0, strlen($id_prefix)) == $id_prefix)
		{
			$id_name = substr($id_name, strlen($id_prefix));
			$id_len = min(hexdec(substr($id_name, 0, 1)), strlen($id_name) - 1);
			$image_id = intval(substr($id_name, 1, $id_len));
			
			if ($image_id > 0)
			{
				$params['image'] = $image_id;
			}
		}
		
		return $params;
	}
}
