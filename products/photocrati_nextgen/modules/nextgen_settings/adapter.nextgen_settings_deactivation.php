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
        
        // then remove all options
        delete_option('ngg_options');
        delete_option('ngg_db_version');
        delete_option('ngg_update_exists');
        delete_option('ngg_next_update');
    
        // now remove the capability
        ngg_remove_capability('NextGEN Gallery overview');
        ngg_remove_capability('NextGEN Use TinyMCE');
        ngg_remove_capability('NextGEN Upload images');
        ngg_remove_capability('NextGEN Manage gallery');
        ngg_remove_capability('NextGEN Edit album');
        ngg_remove_capability('NextGEN Change style');
        ngg_remove_capability('NextGEN Change options');
    }
}
