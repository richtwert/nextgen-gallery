<?php

class Mixin_Url_Manipulation extends Mixin
{
	function join_paths()
	{
		$segments = array();
		$params = func_get_args();
		$this->_flatten_array($params, $segments);

		foreach ($segments as &$segment) {
			if (strpos($segment, '/') === 0) $segment = substr($segment, 1);
			if (substr($segment, -1) == '/') $segment = substr($segment, -1);
		}
		$retval = implode('/', $segments);
		if (strpos($retval, '/') !== 0 && strpos($retval, 'http') === FALSE) $retval = '/'.$retval;

		return $retval;
	}

	function _flatten_array($obj, &$arr)
	{
		if (is_array($obj)) {
			foreach ($obj as $inner_obj) $this->_flatten_array($inner_obj, $arr);
		}
		else $arr[] = $obj;
	}
}