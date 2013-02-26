<?php

class C_Test_Controller extends C_MVC_Controller
{
	/**
	 * Gets an instance of the test controller
	 */
	static $_instances = array();
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function index_action()
	{
		$this->render_view('index');
	}
}