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

		// Install NextGen Basic Compact Album
        $display_type                       = $mapper->find_by_name(NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM);
        if (!$display_type)					$display_type = $mapper->create();
        $display_type->name                 = NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM;
        $display_type->title                = 'NextGEN Basic Compact Album';
        $display_type->preview_image_relpath= $this->find_static_file('compact_preview.gif', TRUE);
        $display_type->entity_types         = array('album', 'gallery');
        $display_type->default_source       = 'albums';
        $mapper->save($display_type);

		// Install NextGen Basic Extended Album
        $display_type                       = $mapper->find_by_name(NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM);
		if (!$display_type)					$display_type = $mapper->create();
        $display_type->name                 = NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM;
        $display_type->title                = 'NextGEN Basic Extended Album';
        $display_type->preview_image_relpath= $this->find_static_file('extended_preview.gif', TRUE);
        $display_type->entity_types         = array('album', 'gallery');
        $display_type->default_source       = 'albums';
		$mapper->save($display_type);
    }
}
