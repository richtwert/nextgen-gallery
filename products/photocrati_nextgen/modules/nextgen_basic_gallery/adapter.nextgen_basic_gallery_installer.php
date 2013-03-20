<?php

class A_NextGen_Basic_Gallery_Installer extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            get_class(),
            get_class(),
            'install_nextgen_basic_gallery'
        );
    }
    
    
    function install_nextgen_basic_gallery()
    {
        $this->object->install_display_type(NEXTGEN_GALLERY_BASIC_THUMBNAILS,
            array(
                'title'					=>	'NextGEN Basic Thumbnails',
                'entity_types'			=>	array('image'),
                'preview_image_relpath'	=>	'nextgen_basic_gallery#thumb_preview.gif',
                'default_source'		=>	'galleries'
            )
        );
        
        $this->object->install_display_type(NEXTGEN_GALLERY_BASIC_SLIDESHOW,
            array(
                'title'					=>	'NextGEN Basic Slideshow',
                'entity_types'			=>	array('image'),
                'preview_image_relpath'	=>	'nextgen_basic_gallery#slideshow_preview.gif',
                'default_source'		=>	'galleries'
            )
        );
    }
}