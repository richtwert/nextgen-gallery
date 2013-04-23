<?php

class A_Gallery_Display_Routes extends Mixin
{
    function initialize()
    {
        $this->object->add_pre_hook(
            'serve_request',
            get_class(),
            get_class(),
            '_add_common_js_route'
        );
    }

    function _add_common_js_route()
    {
        $this->object->route('/ngg_common.js', 'I_Display_Type_Controller#common_js');
    }
}