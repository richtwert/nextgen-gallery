<?php

class C_Dynamic_Stylesheet_Controller extends C_MVC_Controller
{
	static	$_instances				= array();
	var		$_known_templates		= array();
	var		$_app					= NULL;

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Dynamic_Stylesheet_Instance_Methods');
		$this->add_mixin('Mixin_Dynamic_Stylesheet_Actions');
		$this->implement('I_Dynamic_Stylesheet');
	}

	function initialize()
	{
		parent::initialize();
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		$this->_app =$settings->get('dynamic_stylesheet_slug');
	}

	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provides instance methods for the dynamic stylesheet utility
 */
class Mixin_Dynamic_Stylesheet_Instance_Methods extends Mixin
{
	/**
	 * Registers a template with the dynamic stylesheet utility. A template
	 * must be registered before it can be loaded
	 * @param string $name
	 * @param string $template
	 */
	function register($name, $template)
	{
		$this->object->_known_templates[$name] = $template;
	}

	/**
	 * Finds a registered template by name
	 * @param string $name
	 * @return int
	 */
	function get_css_template_index($name)
	{
		return array_search($name, array_keys($this->object->_known_templates));
	}

	function get_css_template($index)
	{
		$keys = array_keys($this->object->_known_templates);
		return $this->object->_known_templates[$keys[$index]];
	}

	/**
	 * Loads a template, along with the dynamic variables to be interpolated
	 * @param string $name
	 * @param array $vars
	 */
	function enqueue($name, $data=array())
	{
		if (($index = $this->object->get_css_template_index($name)) !== FALSE) {
			$lazy_resources	= $this->get_registry()->get_utility('I_Lazy_Resource_Loader');
            if (is_subclass_of($data, 'C_DataMapper_Model')) $data = $data->get_entity();
			$data			= $this->object->encode($data);
			$lazy_resources->style_urls[] = $this->get_router()->get_url("{$this->object->_app}/$index/{$data}");
		}
	}

	function encode($data)
	{
		$lzw			= $this->get_registry()->get_utility('I_Lzw');
		$data			= json_encode($data);
//		$data			= $lzw->compress($data);
		$data			= base64_encode($data);
		$data			= str_replace('/', '\\', $data);
		return $data;
	}


	function decode($data)
	{
		$lzw			= $this->get_registry()->get_utility('I_Lzw');
		$data			= str_replace('\\', '/', $data);
		$data			= base64_decode($data);
//		$data			= $lzw->decompress($data);
		$data			= json_decode($data);
		return $data;
	}
}

/**
 * Provides controller actions for the dynamic stylesheet
 */
class Mixin_Dynamic_Stylesheet_Actions extends Mixin
{
	function index_action()
	{
		$this->set_content_type('css');
		$this->expires("1 year");

		if (($data = $this->param('data')) !== FALSE && ($index = $this->param('index')) !== FALSE) {
			$data = $this->object->decode($data);
			$this->render_view($this->object->get_css_template($index), $data);
		}
	}
}