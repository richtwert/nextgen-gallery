<?php

class A_NextGen_Settings_Deactivation extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'uninstall',
            'NextGEN Settings - Deactivation',
            get_class($this),
            'uninstall_nextgen_settings'
        );
    }

    function uninstall_nextgen_settings()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        foreach ($mapper->select()->limit(null)->run_query() as $library) {
            $mapper->destroy($library);
        }
    }
}
