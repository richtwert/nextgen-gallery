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
//		$this->add_post_hook(
//			'save',
//			'Propagate thumbnail dimensions',
//			'Hook_Propagate_Thumbnail_Dimensions_To_Settings'
//		);
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
	 * Finds display types used to display specific types of entities
	 * @param string|array $entity_type e.g. image, gallery, album
	 * @return array
	 */
	function find_by_entity_type($entity_type, $model=FALSE)
	{
		$find_entity_types = is_array($entity_type) ? $entity_type : array($entity_type);

		$retval = NULL;
		foreach ($this->object->find_all($model) as $display_type) {
			foreach ($find_entity_types as $entity_type) {
				if (in_array($entity_type, $display_type->entity_types)) {
					$retval[] = $display_type;
					break;
				}
			}
		}

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


	/**
	 * Sets default values needed for display types
	 */
	function set_defaults($entity)
	{
		if (!isset($entity->settings)) $entity->settings = array();
		$this->_set_default_value($entity, 'settings', 'alternative_view', '');
		$this->_set_default_value($entity, 'settings', 'show_return_link', '');
		$this->_set_default_value($entity, 'settings', 'alternative_view_link_text', '');
		$this->_set_default_value($entity, 'settings', 'return_link_text', '');
		$this->_set_default_value($entity, 'settings', 'show_alternative_view_link', '');
		$this->_set_default_value($entity, 'preview_image_relpath', '');
		$this->_set_default_value($entity, 'default_source', '');
	}
}
