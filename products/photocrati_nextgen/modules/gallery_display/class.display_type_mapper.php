<?php

/**
 * Provides a datamapper to perform CRUD operations for Display Types
 */
class C_Display_Type_Mapper extends C_CustomPost_DataMapper_Driver
{
	public static $_instances = array();

	function define($context=FALSE)
	{
		parent::define(NULL, array($context, 'display_type'));
		$this->add_mixin('Mixin_Display_Type_Mapper');
		$this->implement('I_Display_Type_Mapper');
		$this->set_model_factory_method('display_type');
		$this->add_post_hook(
			'save',
			'Propagate thumbnail dimensions',
			'Hook_Propagate_Thumbnail_Dimensions_To_Settings'
		);
	}

	function initialize($context=FALSE)
	{
		parent::initialize('display_type');
	}


	/**
	 * Gets a singleton of the mapper
	 * @param string|array $context
	 * @return C_Display_Type_Mapper
	 */
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Display_Type_Mapper($context);
        }
        return self::$_instances[$context];
    }
}


/**
 * Provides instance methods for the display type mapper
 */
class Mixin_Display_Type_Mapper extends Mixin
{
	/**
	 * Locates a Display Type by names
	 * @param string $name
	 */
	function find_by_name($name, $model=FALSE)
	{
		$retval = NULL;
		$this->object->select();
		$this->object->where(array('name = %s', $name));
		$results = $this->object->run_query(FALSE, $model);
		if ($results) $retval = $results[0];
		return $retval;
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