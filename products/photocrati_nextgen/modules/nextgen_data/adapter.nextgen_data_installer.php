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

    function uninstall_nextgen_data($hard)
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
        }
    }
}