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
			if (!isset($url_parts['path']))		$url_parts['path'] = '';
			if (!isset($perm_parts['path']))	$perm_parts['path']= '';
			if (!isset($url_parts['query']))	$url_parts['query'] = '';
			if (!isset($perm_parts['query']))	$perm_parts['query']= '';

			// Ensure that the path is set correctly
			if (isset($perm_parts['path'])) {
				 $url_parts['path'] = $perm_parts['path'];
			}

			// Ensure that the querystring is set correctly
			$url_parts['query'] = $this->object->join_querystrings(
				$url_parts['query'], $perm_parts['query']
			);

			// Rebuild the url
			$this->object->set_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				($retval = $this->object->construct_url_from_parts($url_parts))
			);
		}

		return $retval;
	}

	function get_post_permalink()
	{
		$retval = post_permalink();
		$perm_parts = parse_url($retval);
		$base_parts = parse_url($this->object->get_router()->get_base_url());
		if (!isset($perm_parts['query'])) $perm_parts['query'] = '';
		if (!isset($base_parts['query'])) $base_parts['query'] = '';
		if ($base_parts['path'] != $perm_parts['path']) {
			if (strpos($base_parts['path'], 'index.php') !== false) {
				$perm_parts['path'] = $base_parts['path'];
			}
		}
		$perm_parts['query']	= $this->object->join_querystrings(
			$perm_parts['query'], $base_parts['query']
		);
		$retval = $this->object->construct_url_from_parts($perm_parts);
		return $retval;
	}
}