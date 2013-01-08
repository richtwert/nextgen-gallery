<?php

class A_Shutter_Library_Activation extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'Shutter Library - Activation',
            get_class($this),
            'install_shutter_library'
        );
    }


    function install_shutter_library()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        $mapper->save((object)array(
            'name' =>  'shutter',
            'code' =>  'class="shutterset_%GALLERY_NAME%"',
            'css_stylesheets' =>  NGGALLERY_URLPATH . 'shutter/shutter-reloaded.css',
            'scripts' => NGGALLERY_URLPATH . 'shutter/shutter-reloaded.js' . "\n"
                         . $this->object->static_url('nextgen_shutter.js')
        ));
    }
}
