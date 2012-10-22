<?php

class A_NextGen_Basic_Album extends Mixin
{
    /**
     * Adds a hook to perform validation for albums
     */
    function initialize()
    {
        if ($this->object->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_ALBUM) {
            $this->object->add_pre_hook(
              'validation',
              'NextGEN Basic Album Validation',
              'Hook_NextGen_Basic_Album_Validation'
            );
        }
    }
}

/**
 * Provides validation for NextGen Basic Albums
 */
class Hook_NextGen_Basic_Album_Validation extends Hook
{
    function validation()
    {
      $this->object->validates_presence_of('template');
      $this->object->validates_presence_of('gallery_display_type');
      $this->object->validates_presence_of('galleries_per_page');
      $this->object->validates_numericality_of('galleries_per_page');
    }
}
