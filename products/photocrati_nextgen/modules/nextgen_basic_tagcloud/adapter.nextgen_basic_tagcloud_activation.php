<?php

class A_NextGen_Basic_Tagcloud_Activation extends Mixin
{
    /**
     * Adds the activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'NextGEN Basic Tagcloud - Activation',
            get_class($this),
            'install_nextgen_basic_tagcloud'
        );
    }

    /**
     * Installs the display type for NextGEN Basic Tagcloud
     */
    function install_nextgen_basic_tagcloud()
    {
        $mapper			= $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
        $display_type	= $mapper->find_by_name('photocrati-nextgen_basic_tagcloud', TRUE);
        if (!$display_type) $display_type = new stdClass();

        $display_type->name					 = 'photocrati-nextgen_basic_tagcloud';
        $display_type->title				 = 'NextGEN Basic Tagcloud';
        $display_type->entity_types			 = array('image');
        $display_type->preview_image_relpath = $this->find_static_file('preview.gif', TRUE);

        $mapper->save($display_type);
        unset($mapper);
    }
}
