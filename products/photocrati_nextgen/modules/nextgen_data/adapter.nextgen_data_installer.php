<?php

class A_NextGen_Data_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
            'uninstall',
            get_class(),
            get_class(),
            'uninstall_nextgen_data'
        );
	}

    function uninstall_nextgen_data($hard=FALSE)
    {
        if ($hard) {
            $mappers = array(
                $this->object->get_registry()->get_utility('I_Album_Mapper'),
                $this->object->get_registry()->get_utility('I_Gallery_Mapper'),
                $this->object->get_registry()->get_utility('I_Image_Mapper'),
                $this->object->get_registry()->get_utility('I_Transients')
            );

            foreach ($mappers as $mapper) {
                if ($mapper->has_method('delete_all'))
                    $mapper->delete_all();
                else
                    $mapper->delete()->run_query();
            }

            // Remove ngg tags
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->terms} WHERE term_id IN (SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='ngg_tag')");
            $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy='ngg_tag'");
        }
    }
}