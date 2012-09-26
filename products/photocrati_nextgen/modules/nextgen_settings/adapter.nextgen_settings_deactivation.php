<?php

class A_NextGen_Settings_Deactivation extends Mixin
{
    function initialize()
    {
        $this->object->add_pre_hook(
            'uninstall',
            'NextGEN Settings - Deactivation',
            get_class($this),
            'uninstall_nextgen_settings'
        );

        $this->object->add_post_hook(
            'uninstall',
            'NextGEN Plugin - Deactivation',
            get_class($this),
            'deactivate_nextgen_plugin'
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
        $this->remove_capability('NextGEN Gallery overview');
        $this->remove_capability('NextGEN Use TinyMCE');
        $this->remove_capability('NextGEN Upload images');
        $this->remove_capability('NextGEN Manage gallery');
        $this->remove_capability('NextGEN Edit album');
        $this->remove_capability('NextGEN Change style');
        $this->remove_capability('NextGEN Change options');
    }

    function deactivate_nextgen_plugin()
    {
        deactivate_plugins(plugin_basename(__FILE__));

        print "here";
        exit;
        wp_redirect(get_admin_url() . 'plugins.php');
        throw new E_Clean_Exit();
    }

    function remove_capability($capability)
    {
        // remove the $capability from the classic roles
        $check_order = array('subscriber', 'contributor', 'author', 'editor', 'administrator');
        foreach ($check_order as $role) {
            $role = get_role($role);
            $role->remove_cap($capability);
        }
    }
}
