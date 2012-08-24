<?php

class A_Highslide_Library_Activation extends Mixin
{
    /**
     * Register our activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'Highslide Library - Activation',
            get_class($this),
            'install_highslide_library'
        );
    }

    /**
     * Plugin activation routine - register this with the lightbox library
     */
    function install_highslide_library()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        $mapper->save((object)array(
            'name'            => 'highslide',
            'code'            => 'class="highslide" onclick="return hs.expand(this, galleryOptions);"',
            'css_stylesheets' => PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_CSS_URL,
            'scripts'         => PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_JS_URL . "\n"
                              .  PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_JS_INIT_URL
        ));
    }
}
