<?php

class C_Album_Mapper extends C_DataMapper
{
    static $_instances = array();


	function define($context=FALSE)
	{
		if (!is_array($context)) $context = array($context);
		array_push($context, 'album');
		parent::define('ngg_album', $context);
		$this->get_wrapped_instance()->add_mixin('Mixin_Album_Mapper');
		$this->implement('I_Album_Mapper');
		$this->set_model_factory_method('album');
        $this->get_wrapped_instance()->add_post_hook(
            '_convert_to_entity',
            'Unserialize Galleries',
            'Hook_Unserialize_Album_Galleries',
            'unserialize_galleries'
        );
	}


    /**
     * Returns an instance of the album datamapper
     * @param bool|mixed $context
     * @return C_Album_Mapper
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

/**
 * NextGEN stores all gallery ids for the album in a property called sortorder
 */
class Hook_Unserialize_Album_Galleries extends Hook
{
    function unserialize_galleries($entity)
    {
        if (isset($entity->sortorder) && is_string($entity->sortorder)) {
            $entity->sortorder = $this->object->unserialize($entity->sortorder);
        }
    }
}


/**
 * Provides album-specific methods for the datamapper
 */
class Mixin_Album_Mapper extends Mixin
{
    /**
     * Gets the post title when the Custom Post driver is used
     * @param C_DataMapper_Model|C_Album|stdClass $entity
     * @return string
     */
    function get_post_title($entity)
	{
		return $entity->name;
	}


    /**
     * Sets the defaults for an album
     * @param C_DataMapper_Model|C_Album|stdClass $entity
     */
    function set_defaults($entity)
    {
        $this->object->_set_default_value($entity, 'name', '');
        $this->object->_set_default_value($entity, 'albumdesc', '');
        $this->object->_set_default_value($entity, 'sortorder', '');
        $this->object->_set_default_value($entity, 'previewpic', 0);
        $this->object->_set_default_value(
            $entity,
            'slug',
            nggdb::get_unique_slug( sanitize_title( $entity->name ), 'album' )
        );
    }
}