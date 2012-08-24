<?php

class A_Fancybox_Library_Activation extends Mixin
{
    /**
     * Register our activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'Fancybox Library - Activation',
            get_class($this),
            'install_fancybox_library'
        );
    }

    /**
     * Plugin activation routine - register this with the lightbox library
     */
    function install_fancybox_library()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        $mapper->save((object)array(
            'name'            => 'fancybox',
            'code'            => 'class="ngg-fancybox" rel="%GALLERY_NAME%"',
            'css_stylesheets' => PHOTOCRATI_GALLERY_JQUERY_FANCYBOX_CSS_URL,
            'scripts'         => PHOTOCRATI_GALLERY_JQUERY_EASING_JS_URL . "\n"
                              .  PHOTOCRATI_GALLERY_FANCYBOX_JS_URL . "\n"
                              .  PHOTOCRATI_GALLERY_FANCYBOXY_JS_INIT_URL
        ));
    }
}
