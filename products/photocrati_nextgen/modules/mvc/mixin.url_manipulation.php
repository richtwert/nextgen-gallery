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
			if (substr($segment, -1) == '/') $segment = substr($segment, 0, -1);
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

	/**
	 * Returns the request uri with the parameter segments stripped
	 * @param string $request_uri
	 * @return string
	 */
	function strip_param_segments($request_uri, $remove_slug=TRUE)
	{
		$retval		 = $request_uri ? $request_uri : '/';
		$sep		 = preg_quote(MVC_PARAM_SEPARATOR);
		$param_regex = "#((?<id>\w+){$sep})?(?<key>\w+){$sep}(?<value>\w+)/?$#";
		$slug		 = MVC_PARAM_SLUG && $remove_slug? '/'.preg_quote(MVC_PARAM_SLUG) : '';
		$slug_regex	 = '#'.$slug.'/?$#';

		// Remove all parameters
		while (preg_match($param_regex, $retval, $matches)) {
			$match_regex = '#'.preg_quote(array_shift($matches)).'$#';
			$retval = preg_replace($match_regex, '', $retval);
		}

		// Remove the slug or trailing slash
		if (preg_match($slug_regex, $retval, $matches)) {
			$match_regex = '#'.preg_quote(array_shift($matches)).'$#';
			$retval = preg_replace($match_regex, '', $retval);
		}

		if (!$retval) $retval = '/';

		return $retval;
	}
}