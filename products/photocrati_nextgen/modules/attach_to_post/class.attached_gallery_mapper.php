<?php

class C_Attached_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
    public static $_instances = array();

	function define($context=FALSE)
	{
		parent::define('attached_gallery', array($context, 'attached_gallery'));
		$this->implement(('I_Attached_Gallery_Mapper'));
	}

    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Attached_Gallery_Mapper($context);
        }
        return self::$_instances[$context];
    }
}
