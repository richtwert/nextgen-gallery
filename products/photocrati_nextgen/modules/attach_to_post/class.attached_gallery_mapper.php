<?php

/**
 * Provides a datamapper utility for attached galleries
 */
class C_Attached_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
    public static $_instances = array();

	/**
	 * Define the object
	 */
	function define()
	{
		parent::define();
		$this->implement(('I_Attached_Gallery_Mapper'));
	}


	function initialize($context = FALSE)
	{
		// Tells the CustomPost driver what the custom post will be called, as
		// well sets a context
		parent::define('attached_gallery', array($context, 'attached_gallery'));

		// Tells the CustomPost driver what property to use
		// as the value for the "post_title" column
		$this->_post_title_field = 'title';
	}

	/**
	 * Gets a singleton of the mapper
	 * @param string|array $context
	 * @return C_Attached_Gallery_Mapper
	 */
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Attached_Gallery_Mapper($context);
        }
        return self::$_instances[$context];
    }
}
