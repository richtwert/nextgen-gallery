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
    function uninstall()
    {
    }

    function check_uninstall()
    {
        $this->object->add_mixin('Mixin_MVC_Controller_Rendering');
        $this->object->render_partial(
            'check_uninstall',
            array(
                'deactivate_label'  => _('Only deactivate'),
                'uninstall_label'   => _('Remove all NextGEN data and deactivate the plugin'),
                'uninstall_warning' => _('Deactivating NextGEN will leave your data intact. Choose "uninstall" to remove your galleries, albums, etc.')
            )
        );
        throw new E_Clean_Exit();
    }
}
