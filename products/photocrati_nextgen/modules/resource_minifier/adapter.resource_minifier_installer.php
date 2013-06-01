<?php

class A_Resource_Minifier_Installer extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'uninstall',
            get_class().'::Uninstall',
            get_class(),
            'resource_minifier_uninstall_actions'
        );
    }

    function resource_minifier_uninstall_actions($product, $hard = FALSE)
    {
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
        $manager = $this->get_registry()->get_utility('I_Resource_Manager');
        $manager->flush_cache();
    }
}
