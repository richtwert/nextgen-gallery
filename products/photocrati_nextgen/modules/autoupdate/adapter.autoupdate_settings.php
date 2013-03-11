<?php

class A_AutoUpdate_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('autoupdate_api_url', 'http://members.photocrati.com/api/');
	}
}