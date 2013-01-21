<?php

class A_NextGen_Basic_Singlepic_Activation extends Mixin
{
    /**
     * Adds the activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'NextGEN Basic Singlepic - Activation',
            get_class($this),
            'install_nextgen_basic_singlepic'
        );
    }

    /**
     * Installs the display type for NextGEN Basic Singlepic
     */
    function install_nextgen_basic_singlepic()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
        $display_type = $mapper->find_by_name(NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME, TRUE);
        if (!$display_type)
            $display_type = new stdClass();
        $display_type->name                  = NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME;
        $display_type->title                 = 'NextGEN Basic Singlepic';
        $display_type->entity_types          = array('image');
        $display_type->preview_image_relpath = $this->find_static_file('preview.gif', TRUE);
        $display_type->default_source        = 'galleries';
        $mapper->save($display_type);
        unset($mapper);
    }
}
