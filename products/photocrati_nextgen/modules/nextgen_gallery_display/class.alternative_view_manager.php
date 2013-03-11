<?php

class C_Alternative_View_Manager extends C_Component
{
	static $_instances = array();
	var	   $_views	   = array();

	/**
	 * Gets an instance of the view manager
	 * @param string $context
	 * @return C_Alternative_View_Manager
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Alternative_View_Manager');
		$this->implement('I_Alternative_View_Manager');
	}
}

class Mixin_Alternative_View_Manager extends Mixin
{
	function add($type, $name, $title)
	{
		if ($this->object->get_alternative_view_count($type))
			$this->object->_views[$type] = array();

		$this->object->_views[$type][$name] = array(
			'type'	=>	$type,
			'name'	=>	$name,
			'title'	=>	$title
		);

		return $this->get_alternative_view_count($type);
	}

	function get_alternative_view_count($type=FALSE)
	{
		$retval = 0;

		if ($type) {
			if (isset($this->object->_views[$type])) {
				$retval = count($this->object->_views[$type]);
			}
		}
		else {
			foreach ($this->object->_views as $key => $views) {
				$retval .= count($views);
			}
		}

		return $retval;
	}

	/**
	 * Returns all views
	 * @param string $type
	 * @return array
	 */
	function get_all($type=FALSE, $omit=array())
	{
		$retval = array();

		if ($type) {
			if ($this->object->get_alternative_view_count($type))
				$retval = $this->object->_views[$type];
		}
		else {
			foreach ($this->object->_views as $key => $views) {
				foreach ($views as $view) {
					$retval[$view[$name]] = $view;
				}
			}
		}

		foreach ($omit as $view) {
			unset($retval[$view]);
		}

		return $retval;
	}

}