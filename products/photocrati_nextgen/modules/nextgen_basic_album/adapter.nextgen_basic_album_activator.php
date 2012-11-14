<?php

class A_NextGen_Basic_Album_Activator extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'NextGEN Basic Album - Activation',
            get_class($this),
            'install_nextgen_basic_album'
        );
    }

    function install_nextgen_basic_album()
    {
        $mapper                             = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
        $display_type                       = $mapper->find_by_name(PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM);
        if (!$display_type) $display_type   = new stdClass();
        $display_type->name                 = PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM;
        $display_type->title                = 'NextGEN Basic Album';
        $display_type->preview_image_relpath= $this->find_static_file('preview.gif');
        $display_type->entity_types         = array('album', 'gallery');
        $mapper->save($display_type);
    }
}
