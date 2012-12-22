<?php

class A_WordPress_Router extends Mixin
{
	var $_site_url = FALSE;

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