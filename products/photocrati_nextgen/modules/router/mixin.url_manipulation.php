<?php

class Mixin_Url_Manipulation extends Mixin
{
	function join_paths()
	{
		$args = func_get_args();
		return $this->get_registry()->get_utility('I_Fs')->join_paths($args);
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

		// If the url has a path, then we can remove a segment
		if (isset($parts['path'])) {
			if (substr($segment, -1) == '/') $segment = substr($segment, -1);
			$segment = preg_quote($segment, '#');
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
			isset($parts['path']) ? $parts['path'] : ''
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
		$settings	 = $this->get_registry()->get_utility('I_Settings_Manager');
		$sep		 = preg_quote($settings->router_param_separator, '#');
		$param_regex = "#((?<id>\w+){$sep})?(?<key>\w+){$sep}(?<value>.+)/?$#";
		$slug		 = $settings->router_param_slug && $remove_slug ? '/' . preg_quote($settings->router_param_slug,'#') : '';
		$slug_regex	 = '#'.$slug.'/?$#';

		// Remove all parameters
		while (preg_match($param_regex, $retval, $matches)) {
			$match_regex = '#'.preg_quote(array_shift($matches),'#').'$#';
			$retval = preg_replace($match_regex, '', $retval);
		}

		// Remove the slug or trailing slash
		if (preg_match($slug_regex, $retval, $matches)) {
			$match_regex = '#'.preg_quote(array_shift($matches),'#').'$#';
			$retval = preg_replace($match_regex, '', $retval);
		}

		// If there's a slug, we can assume everything after is a parameter,
		// even if it's not in our desired format.
		$retval = preg_replace('#'.$slug.'.*$#', '', $retval);

		if (!$retval) $retval = '/';

		return $retval;
	}
}