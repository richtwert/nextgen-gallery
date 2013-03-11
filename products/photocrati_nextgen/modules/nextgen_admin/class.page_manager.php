<?php

class C_Page_Manager extends C_Component
{
	static $_instances = array();
	var $_pages = array();

	/**
	 * Gets an instance of the Page Manager
	 * @param string $context
	 * @return C_Page_Manager
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	/**
	 * Defines the instance of the Page Manager
	 * @param type $context
	 */
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Page_Manager');
		$this->implement('I_Page_Manager');
	}
}

class Mixin_Page_Manager extends Mixin
{
	function add($slug, $adapter, $parent=NULL, $add_menu=TRUE)
	{
		$this->object->_pages[$slug] = array(
			'adapter'	=>	$adapter,
			'parent'	=>	$parent,
			'add_menu'	=>	$add_menu
		);
	}

	function remove_page($slug)
	{
		unset($this->object->_pages[$slug]);
	}

	function get_all()
	{
		return $this->object->_pages;
	}

	function setup()
	{
		$registry		= $this->get_registry();
		$controllers	= array();
		foreach ($this->object->_pages as $slug => $properties) {
			$registry->add_adapter(
				'I_NextGen_Admin_Page',
				$properties['adapter'],
				$slug
			);
			$controllers[$slug] = $registry->get_utility(
				'I_NextGen_Admin_Page',
				$slug
			);
			if ($properties['add_menu']) {
				add_submenu_page(
					$properties['parent'],
					$controllers[$slug]->get_page_title(),
					$controllers[$slug]->get_page_heading(),
					$controllers[$slug]->get_required_permission(),
					$slug,
					array(&$controllers[$slug], 'index_action')
				);
			}
		}
	}
}