<?php

class C_Frame_Event_Publisher extends C_Component
{
	static $_instances = array();
	var $cookie_name = 'frame_events';

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Frame_Event_Publisher');
		$this->implement('I_Frame_Event_Publisher');
	}

	/**
	 * Gets an instance of the publisher
	 * @param string $context
	 * @return C_Frame_Event_Publisher
	 */
	static function get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

class Mixin_Frame_Event_Publisher extends Mixin
{
	/**
	 * Encodes data for a cookie
	 * @param array $data
	 * @return string
	 */
	function _encode($data)
	{
		return rawurlencode(json_encode($data));
	}

	/**
	 * Decodes data from a cookie
	 * @param string $data
	 * @return array
	 */
	function _decode($data)
	{
		return (array)json_decode(rawurldecode($data));
	}

	/**
	 * Gets a cookie of particular name
	 * @param string $name
	 * @return array
	 */
	function _get_cookie($name)
	{
		$retval = array();
		if (isset($_COOKIE[$name])) $retval = $this->object->_decode($_COOKIE[$name]);
		foreach ($retval as $key => $value) {
			if (!is_array($value)) $retval[$key] = (array)$value;
		}
		return $retval;
	}

	/**
	 * Sets a cookie
	 * @param string $name
	 * @param array $value
	 */
	function _set_cookie($name, $value)
	{
		setrawcookie($name, $this->object->_encode($value));
	}


	function add_event($data)
	{
		$cookie = $this->object->_get_cookie($this->object->cookie_name);

		// We'll store events in the current context
		if (!isset($cookie[$this->object->context])) {
			$cookie[$this->object->context] = array();
		}

		// Set the cookie
		$cookie[$this->object->context][md5(serialize($data))] = $data;
		$this->object->_set_cookie($this->object->cookie_name, $cookie);
		ob_flush();
		flush();

		return $cookie;
	}
}
