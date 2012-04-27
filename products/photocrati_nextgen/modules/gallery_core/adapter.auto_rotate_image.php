<?php

class A_Auto_Rotate_Image extends Hook
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save',
            'auto_rotate_image',
            get_class($this), 
            'rotate_image'
        );
    }
    
    function rotate_image()
    {
        if (!$this->object->is_valid()) return;
        include_once(path_join(NGGALLERY_ABSPATH, 'admin/functions.php'));
        nggAdmin::rotate_image($this->object->id());
    }
}