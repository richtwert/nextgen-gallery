<?php

class Mixin_Url_Manipulation extends Mixin
{
	function join_paths()
	{
		$segments = array();
		$retval = array();
		$params = func_get_args();
		$this->_flatten_array($params, $segments);

		foreach ($segments as $segment) {
			if (strpos($segment, '/') === 0)
                $segment = substr($segment, 1);
			if (substr($segment, -1) === '/')
                $segment = substr($segment, 0, -1);
			if ($segment) $retval[] = $segment;
		}
		$retval = implode('/', $retval);
		if (strpos($retval, '/') !== 0 && strpos($retval, 'http') === FALSE)
            $retval = '/'.$retval;
		return $retval;
	}

	function _flatten_array($obj, &$arr)
	{
		if (is_array($obj)) {
			foreach ($obj as $inner_obj) $this->_flatten_array($inner_obj, $arr);
		}
		elseif ($obj) $arr[] = $obj;
	}

	/**
	 * Removes a segment from a url
	 * @param string $segment
	 * @param string $url
	 * @return string
	 */
	function remove_url_segment($segment, $url)
	{
		$retval = $url;
		$parts	= parse_url($url);
		$segment= preg_quote($segment, '#');

		// If the url has a path, then we can remove a segment
		if (isset($parts['path'])) {
			if (substr($segment, -1) == '/') $segment .= '?';
			if (preg_match("#{$segment}#", $parts['path'], $matches)) {
				$parts['path'] = str_replace(
					'//',
					'/',
					str_replace($matches[0], '', $parts['path'])
				);
				$retval = $this->object->construct_url_from_parts($parts);
			}
		}
		return $retval;
	}

	function join_querystrings()
	{
		$parts	= array();
		$retval = array();
		$params = func_get_args();
		$this->_flatten_array($params, $parts);
		foreach ($parts as $part) {
			$retval[] = str_replace(
				array('?', '&', '/'),
				array('', '', ''),
				$part
			);
		}
		return implode('&', array_unique($parts));
	}


	/**
	 * Constructs a url from individual parts, created by parse_url
	 * @param array $parts
	 * @return string
	 */
	function construct_url_from_parts($parts)
	{
		$retval =  $this->object->join_paths(
			isset($parts['scheme']) && $parts['host'] ?
				"{$parts['scheme']}://{$parts['host']}" : '',
			$parts['path']
		);
		if (isset($parts['query']) && $parts['query']) $retval .= "?{$parts['query']}";

		return $retval;
	}

	/**
	 * Returns the request uri with the parameter segments stripped
	 * @param string $request_uri
	 * @return string
	 */
	function strip_param_segments($request_uri, $remove_slug=TRUE)
	{
		$retval		 = $request_uri ? $request_uri : '/';
		$sep		 = preg_quote(MVC_PARAM_SEPARATOR, '#');
		$param_regex = "#((?<id>\w+){$sep})?(?<key>\w+){$sep}(?<value>.+)/?$#";
		$slug		 = MVC_PARAM_SLUG && $remove_slug ? '/' . preg_quote(MVC_PARAM_SLUG) : '';
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

		// If there's a slug, we can assume everything after is a parameter,
		// even if it's not in our desired format.
		$retval = preg_replace('#'.$slug.'.*$#', '', $retval);

		if (!$retval) $retval = '/';

		return $retval;
	}
}