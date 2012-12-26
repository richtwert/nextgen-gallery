<?php

class A_WordPress_Router extends Mixin
{
	var $_site_url = FALSE;

	function initialize()
	{
		$this->object->add_post_hook(
			'get_url',
			'Construct url for WordPress, considering permalinks',
			get_class(),
			'_modify_url_for_wordpress'
		);
	}

	function _modify_url_for_wordpress()
	{
		// Get the method to be returned
		$retval = $this->object->get_method_property(
			$this->method_called,
			ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// Determine whether the url is a directory or file on the filesystem
		// If so, then we do NOT need /index.php as part of the url
		$filename = str_replace($this->object->get_base_url().'/', ABSPATH, $retval);
		if ($retval && file_exists($filename)) {
			$retval = str_replace('index.php/', '', $retval);
			$this->object->set_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				$retval
			);
		}

		return $retval;
	}


	function get_base_url()
	{
		if (!$this->_site_url) {
			$this->_site_url = site_url();
			if (!get_option('permalink_structure')) {
				if (substr($this->_site_url, -1) == '/')
					$this->_site_url .= 'index.php';
				else
					$this->_site_url .= '/index.php';
			}
		}

		return $this->_site_url;
	}
}