<?php

class A_NextGen_Data_Deactivation extends Mixin
{
    function initialize()
    {
        $this->object->add_pre_hook(
            'uninstall',
            'NextGEN Data - Deactivation',
            get_class($this),
            'uninstall_nextgen_data'
        );
    }

    function uninstall_nextgen_data()
    {
        $mappers = array(
            'I_Image_Mapper',
            'I_Gallery_Mapper'
        );
        foreach ($mappers as $map_name) {
            $mapper = $this->object->get_registry()->get_utility($map_name);
            $key = $mapper->get_primary_key_column();
            foreach ($mapper->select($key)->run_query() as $entity) {
                $mapper->destroy($entity);
            }
        }
    }
}
