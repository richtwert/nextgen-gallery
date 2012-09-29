<?php

class Mixin_Dynamic_Thumbnails_Manager extends Mixin
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
			
			return $this->object->_get_params_sanitized($params);
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
			'max_value_length' => 15, // Note: this can't be increased beyond 15, as a single hexadecimal character is used to encode the value length in names. Increasing it over 15 requires changing the algorithm to use an arbitrary letter instead of a hexadecimal digit (this would bump max length to 35, 9 numbers + 26 letters)
		);
	}
	
	function get_name_from_params($params, $only_size_name = false, $id_in_name = true)
	{
		$prefix_list = $this->object->_get_name_prefix_list();
		$id_prefix = $prefix_list['id'];
		$size_prefix = $prefix_list['size'];
		$flags_prefix = $prefix_list['flags'];
		$flags = $prefix_list['flag'];
		$max_value_length = $prefix_list['max_value_length'];
		
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
		
		// if $only_size_name is false then we include the file name and image id for the image
		if (!$only_size_name)
		{
			if (is_int($image))
			{
        $imap = $this->object->get_registry()->get_utility('I_Image_Mapper');
        $image = $imap->find($image);
			}
			
			if ($image != null)
			{
				// this is used to remove the extension and then add it back at the end of the name
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
					$id_len = min($max_value_length, strlen($image_id));
					$id_len_hex = dechex($id_len);
					
					// sanity check, should never occurr if $max_value_length is not messed up, ensure only 1 character is used to encode length or else skip parameter
					if (strlen($id_len_hex) == 1)
					{
						$name .= $id_prefix . $id_len . substr($image_id, 0, $id_len);
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
					// only strings or ints allowed, sprintf is required because intval(0) returns '' and not '0'
					$flag_value = intval($flag_value);
					$flag_value = sprintf('%d', $flag_value);
				}
			}
			
			$flag_value = strval($flag_value);
			$flag_len = min($max_value_length, strlen($flag_value));
			$flag_len_hex = dechex($flag_len);
			
			// sanity check, should never occurr if $max_value_length is not messed up, ensure only 1 character is used to encode length or else skip parameter
			if (strlen($flag_len_hex) == 1)
			{
				$name .= $flag_prefix . $flag_len . substr($flag_value, 0, $flag_len);
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
		$max_value_length = $prefix_list['max_value_length'];
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
			// get the length of the flag id (the key in the $flags array) in the string (how many characters to consume)
			$flag_id_len = $prefix_list['flag_len'];
			$params_str = substr($size_name, strlen($size_prefix));
			$params_parts = explode('-', $params_str);
			
			// $param_part is a single param, separated by '-'
			foreach ($params_parts as $param_part)
			{
				// Parse WxHxQ - Q=quality
				$param_size = explode('x', $param_part);
				$param_size_count = count($param_size);
				
				if (substr($param_part, 0, strlen($flags_prefix)) == $flags_prefix)
				{
					/* Set flags, using $flags keys as prefixes */
					
					// move string pointer up (after the main flags prefix)
					$param_flags = substr($param_part, strlen($flags_prefix));
					$param_flags_len = strlen($param_flags);
					$flags_todo = $flags;
					
					while (true)
					{
						// ensure we don't run into an infinite loop ;)
						if (count($flags_todo) == 0 || strlen($param_flags) == 0)
						{
							break;
						}
						
						// get the flag prefix (a key in the $flags array) using flag id length
						$flag_prefix = substr($param_flags, 0, $flag_id_len);
						// move string pointer up (after the single flag prefix)
						$param_flags = substr($param_flags, $flag_id_len);
					
						// get the length of the flag value in the string (how many characters to consume)
						// flag value length is stored in a single hexadecimal character next to the flag prefix
						$flag_value_len = min(hexdec(substr($param_flags, 0, 1)), min($max_value_length, strlen($param_flags) - 1));
						// get the flag value
						$flag_value = substr($param_flags, 1, $flag_value_len);
						// move string pointer up (after the entire flag)
						$param_flags = substr($param_flags, $flag_value_len + 1);
						
						// make sure the flag is supported
						if (isset($flags[$flag_prefix]))
						{
							$flag_name = $flags[$flag_prefix];
							
							if (is_numeric($flag_value))
							{
								// convert numerical flags to integers
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
			// move string pointer up (after the prefix)
			$id_name = substr($id_name, strlen($id_prefix));
			// get the length of the image id in the string (how many characters to consume)
			$id_len = min(hexdec(substr($id_name, 0, 1)), min($max_value_length, strlen($id_name) - 1));
			// get the id based on old position and id length
			$image_id = intval(substr($id_name, 1, $id_len));
			
			if ($image_id > 0)
			{
				$params['image'] = $image_id;
			}
		}
		
		return $this->object->_get_params_sanitized($params);
	}
	
	function is_size_dynamic($name, $is_only_size_name = false)
	{
		$params = $this->object->get_params_from_name($name, $is_only_size_name);
		
		if (isset($params['width']) && isset($params['height']))
		{
			return true;
		}
		
		return false;
	}
}

class C_Dynamic_Thumbnails_Manager extends C_Component
{
    static $_instances = array();
    
    function define($context=FALSE)
    {
        parent::define($context);

        $this->implement('I_Dynamic_Thumbnails_Manager');
        $this->add_mixin('Mixin_Dynamic_Thumbnails_Manager');
    }

    static function get_instance($context = False)
    {
		if (!isset(self::$_instances[$context]))
		{
				self::$_instances[$context] = new C_Dynamic_Thumbnails_Manager($context);
		}

		return self::$_instances[$context];
    }
}
