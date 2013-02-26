<?php

class C_NextGen_Deactivator extends C_Component
{
    static $_instances = array();

    function define($context=FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_NextGen_Deactivator');
        $this->implement('I_NextGen_Deactivator');
    }

    /**
     * Gets the class instance
     * @param string|array|FALSE $context
     * @return C_NextGen_Deactivator
     */
    static function get_instance($context=FALSE)
    {
        $klass = get_class();
        if (!isset(self::$_instances[$context])) {
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
}

/**
 * Provides the install function, which other modules can provide hooks for to
 * run activation routines
 */
class Mixin_NextGen_Deactivator extends Mixin
{
    function deactivate()
    {
        deactivate_plugins(array(NEXTGEN_GALLERY_PLUGIN_BASENAME));
        delete_option('ngg_init_check');
        delete_option('ngg_update_exists');
    }

    function flush_cache()
    {
    }

    function uninstall()
    {
    }
}
