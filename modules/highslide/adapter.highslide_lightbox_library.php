<?php

class A_Highslide_Lightbox_Library extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            '_load_libraries',
            'Adds support for Highslide',
            get_class(),
            'add_highslide_support'
        );
    }
    
    
    function add_highslide_support()
    {
        // Get libraries
        $libraries = $this->object->get_method_property(
            $this->method_called,
            'return_value'
        );
        
        // Add highslide if it doesn't exist already
        if (!isset($libraries['highslide'])) {
            
            $graphics_dir = PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_GRAPHICS_DIR;
            
            $libraries['highslide'] = array(
                'script'            =>  'highslide',
                'style'             =>  'highslide',
                'javascript_code'   =>  implode("\n", array(
                    "hs.graphicsDir = '{$graphics_dir}';",
                    "var galleryOptions = {
                        slideshowGroup: 'gallery',
                        wrapperClassName: 'dark',
                        //outlineType: 'glossy-dark',
                        dimmingOpacity: 0.8,
                        align: 'center',
                        transitions: ['expand', 'crossfade'],
                        fadeInOut: true,
                        wrapperClassName: 'borderless floating-caption',
                        marginLeft: 100,
                        marginBottom: 80,
                        numberPosition: 'caption'
                    };",
                    "hs.addSlideshow({ interval: 5000, repeat: true, useControls: true, fixedControls: true, overlayOptions: {opacity: .6, position: 'top center', hideOnMouseOut: true} });"
                )),
                'html'              =>  'class="highslide" onclick="return hs.expand(this, galleryOptions);"'
            );
            
            // Set the new method return value
            $this->object->set_method_property(
                $this->method_called,
                'return_value',
                $libraries
            );
            
        }
        
        return $libraries;
    }
}