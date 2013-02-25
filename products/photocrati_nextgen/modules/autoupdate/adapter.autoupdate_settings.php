<?php

class A_AutoUpdate_Settings extends Mixin
{
	function initialize()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		$settings->autoupdate_api_url = 'http://members.photocrati.com/api/';
	}
}