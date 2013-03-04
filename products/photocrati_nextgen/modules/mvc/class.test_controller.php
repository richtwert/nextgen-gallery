<?php

class C_Test_Controller extends C_Http_Response_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->implement('I_Test_Controller');
	}

	function index_action()
	{
		echo "Here is your value: {$this->param('value')}";
	}

	static function get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}
