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
		if (in_array($entity->name, array(
		  NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM,
		  NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM))) {

			// Set defaults for both display (album) types
            $settings = $this->object->get_registry()->get_utility('I_Settings_Manager');
            $this->object->_set_default_value($entity, 'settings', 'galleries_per_page', $settings->galPagedGalleries);
            $this->object->_set_default_value($entity, 'settings', 'disable_pagination',  0);
            $this->object->_set_default_value($entity, 'settings', 'gallery_display_type', NEXTGEN_GALLERY_BASIC_THUMBNAILS);

			// Set the correct default ngglegacy template
			if ($entity->name == NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM) {
				$this->object->_set_default_value($entity, 'settings', 'template', 'compact');
			}
			else {
				$this->object->_set_default_value($entity, 'settings', 'template', 'extend');
			}
        }


    }
}