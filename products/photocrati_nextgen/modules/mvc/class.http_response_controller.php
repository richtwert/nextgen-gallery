<?php

class C_Http_Response_Controller extends C_MVC_Controller
{
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Http_Response_Actions');
		$this->implement('I_Http_Response');
	}
}

class Mixin_Http_Response_Actions extends Mixin
{
	function http_301_action()
	{

	}

	function http_302_action()
	{

	}

	function http_500_action()
	{

	}

	function http_404_action()
	{

	}
}