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
			$url = rtrim(post_permalink(), '/');
			if (strpos($retval, $url) === FALSE) {
				$retval = str_replace(site_url(), $url, $retval);
				if (strpos($retval, 'index.php') === FALSE)
					$retval = str_replace(site_url(), $this->get_router()->get_base_url(), $retval);
				$this->object->set_method_property(
					$this->method_called,
					ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
					$retval
				);
			}
		}

		return $retval;
	}
}