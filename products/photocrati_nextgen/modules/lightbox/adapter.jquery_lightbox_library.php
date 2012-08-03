<?php

class A_JQuery_Lightbox_Library extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            '_load_libraries', 
            "Adds lightbox support", 
            get_class(),
            'add_jquery_lightbox_support'
        );
        
    }
    
    function add_jquery_lightbox_support()
    {
        // Get libraries
        $libraries = $this->object->get_method_property(
            $this->method_called,
            'return_value'
        );

        $loading_img_url = PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_LOADING_URL;
        $close_btn_url   = PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_CLOSE_URL;
        $prev_btn_url    = PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_PREV_URL;
        $next_btn_url    = PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_NEXT_URL;
        $blank_img_url   = PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BLANK_URL;


        // Add jquery lightbox 
        if (!isset($libraries['lightbox'])) {
            $libraries['lightbox'] = array(
                'script'            => 'jquery.lightbox',
                'style'             => 'jquery.lightbox',
                'javascript_code'   => "jQuery(function($){
                    $('.ngg_lightbox').lightBox({
                        imageLoading:   '{$loading_img_url}',
                        imageBtnClose:  '{$close_btn_url}',
                        imageBtnPrev:   '{$prev_btn_url}',
                        imageBtnNext:   '{$next_btn_url}',
                        imageBlank:     '{$blank_img_url}',
                    });
                });",
                'html'              => "class='ngg_lightbox'"

            );

            $this->object->set_method_property(
                $this->method_called,
                'return_value',
                $libraries
            );
        }

        return $libraries;
    }
    
}
