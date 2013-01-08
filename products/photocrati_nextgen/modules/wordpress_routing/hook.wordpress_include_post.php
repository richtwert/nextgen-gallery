<?php

class Hook_WordPress_Include_Post extends Hook
{
	function remove_parameter($key, $id=NULL, $url=FALSE)
	{
		return $this->_modify_generated_url($url);
	}

    /**
     * Prefixes the Wordpress post permalink into the URL when the viewer is currently on the front page
     */
    function _modify_generated_url($url)
	{
		// Get the method's return value, which is the generated url
		$retval = $this->object->get_method_property(
			$this->method_called, ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// Are we generating a custom url, and are we about to display it on the front page?
		if ((is_front_page() OR is_home()) && $url) {
			$url_parts	= parse_url($retval);
			$perm_parts = parse_url($this->get_post_permalink());
			if (!isset($url_parts['path']))		$url_parts['path'] = '/';
			if (!isset($perm_parts['path']))	$perm_parts['path']= '/';
			if (!isset($url_parts['query']))	$url_parts['query'] = '';
			if (!isset($perm_parts['query']))	$perm_parts['query']= '';

			// Ensure that the path is set correctly
			$perm_path	= preg_quote($perm_parts['path'], '#');
			if (!preg_match("#^{$perm_path}#", $url_parts['path'])) {
				$url_parts['path'] = $this->object->join_paths(
					$perm_parts['path'],
					$url_parts['path']
				);
			}

			// Ensure that the querystring is set correctly
			$perm_qs	= preg_quote($perm_parts['query'], '#');
			if (!preg_match("#&?{$perm_qs}#", $url_parts['query'])) {
				if ($url_parts['query']) $url_parts['query'] .= '&'.$perm_parts['query'];
				else $url_parts['query'] = $perm_parts['query'];
			}

			// Rebuild the url
			$this->object->set_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				($retval = $this->object->join_paths(
					"{$url_parts['scheme']}://{$url_parts['host']}",
					$url_parts['path'],
					'?'.$url_parts['query']
				))
			);
		}

		return $retval;
	}

	function get_post_permalink()
	{
		$retval = post_permalink();
		if (strpos($retval, 'index.php') === FALSE)
			$retval = str_replace(site_url(), $this->get_router()->get_base_url(), $retval);
		return $retval;
	}
}