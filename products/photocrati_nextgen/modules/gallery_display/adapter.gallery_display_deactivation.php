<?php

class A_Gallery_Display_Deactivation extends Mixin
{
    function initialize()
    {
        $this->object->add_pre_hook(
            'uninstall',
            'Gallery Display - Deactivation',
            get_class($this),
            'uninstall_gallery_display'
        );
    }

    function uninstall_gallery_display()
    {
        $mappers = array(
            'I_Displayed_Gallery_Mapper',
            'I_Display_Type_Mapper'
        );
        foreach ($mappers as $map_name) {
            $mapper = $this->object->get_registry()->get_utility($map_name);
            foreach ($mapper->select()->limit(null)->run_query() as $library) {
                $mapper->destroy($library);
            }
        }
    }
}
