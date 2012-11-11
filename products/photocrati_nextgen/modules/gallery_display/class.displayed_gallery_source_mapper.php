<?php

class C_Displayed_Gallery_Source_Mapper extends C_CustomPost_DataMapper_Driver
{

	// We maintain singleton objects of this class for each particular context
	// used
	static $_instances = array();

	/**
	 * Returns an instance of this class using a particular context
	 * @param string|array|bool $context
	 * @return C_Displayed_Gallery_Source_Mapper
	 */
	public static function get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	/**
	 * Define the mapper
	 * @param string|array|bool $context
	 */
	function define($context=FALSE)
	{
		$object_name = 'gal_display_source';

		// Add the object name to the context of the object as well
		// This allows us to adapt the driver itself, if required
		if (!is_array($context)) $context = array($context);
		array_push($context, $object_name);

		// Define the driver
		parent::define($object_name, $context);

		// Set the factory method for instantiating models
		$this->set_model_factory_method('displayed_gallery_source');

		// Add the mixin of instance methods
		$this->add_mixin('Mixin_Displayed_Gallery_Source_Mapper');

		// Implement the interface
		$this->implement('I_Displayed_Gallery_Source_Mapper');
	}

	/**
	 * Initializes the datamapper driver
	 */
	function initialize()
	{
		parent::initialize('gal_display_source');
	}
}

class Mixin_Displayed_Gallery_Source_Mapper extends Mixin
{
	/**
	 * Provides a means to find a displayed gallery source with a particular name
	 * @param string $name
	 */
	function find_by_name($name, $return_models=FALSE)
	{
		return $this->object->find_all(array("name = %s", $name), $return_models);
	}

	/**
	 * Uses the title attribute as the post title
	 * @param stdClass $entity
	 * @return string
	 */
	function get_post_title($entity)
	{
		return $entity->title;
	}
}