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
    
    
    function install_nextgen_basic_gallery($product)
    {
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
        $this->object->install_display_type(NEXTGEN_GALLERY_BASIC_THUMBNAILS,
            array(
                'title'					=>	'NextGEN Basic Thumbnails',
                'entity_types'			=>	array('image'),
                'preview_image_relpath'	=>	'nextgen_basic_gallery#thumb_preview.jpg',
                'default_source'		=>	'galleries',
								'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE
            )
        );
        
        $this->object->install_display_type(NEXTGEN_GALLERY_BASIC_SLIDESHOW,
            array(
                'title'					=>	'NextGEN Basic Slideshow',
                'entity_types'			=>	array('image'),
                'preview_image_relpath'	=>	'nextgen_basic_gallery#slideshow_preview.jpg',
                'default_source'		=>	'galleries',
								'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 10
            )
        );
    }
}
