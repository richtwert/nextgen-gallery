<?php

/**
 * Provides a datamapper to perform CRUD operations for Display Types
 */
class C_Display_Type_Mapper extends C_CustomPost_DataMapper_Driver
{
	public static $_instances = array();

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Display_Type_Mapper');
		$this->implement('I_Display_Type_Mapper');
		$this->set_model_factory_method('display_type');
	}

	/**
	 * Initializes the mapper
	 * @param string|array|FALSE $context
	 */
	function initialize($context = FALSE)
	{
		// Tells the CustomPost driver what the custom post will be called, as
		// well sets a context
		parent::define('display_type', array($context, 'display_type'));

		// Tells the CustomPost driver what property to use
		// as the value for the "post_title" column
		$this->_post_title_field = 'title';
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
}