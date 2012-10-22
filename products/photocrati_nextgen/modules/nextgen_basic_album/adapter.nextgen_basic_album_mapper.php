<?php

class A_NextGen_Basic_Album_Mapper extends Mixin
{
    /**
     * Adds a hook for setting default values
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'set_defaults',
            'NextGen Basic Album Defaults',
            'Hook_NextGen_Basic_Album_Defaults',
            'set_defaults'
        );
    }
}


class Hook_NextGen_Basic_Album_Defaults extends Hook
{
    function set_defaults($entity)
    {
        if ($entity->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM) {
            $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
            $this->object->_set_default_value($entity, 'settings', 'galleries_per_page', $settings->galPagedGalleries);
            $this->object->_set_default_value($entity, 'settings', 'disable_pagination',  0);
            $this->object->_set_default_value($entity, 'settings', 'gallery_display_type', PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS);
            $this->object->_set_default_value($entity, 'settings', 'template', 'extend');
        }
    }
}