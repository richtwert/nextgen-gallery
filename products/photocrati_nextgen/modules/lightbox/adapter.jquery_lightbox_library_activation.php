<?php

class A_JQuery_Lightbox_Library_Activation extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            "JQuery Lightbox Library - Activation",
            get_class($this),
            'install_jquery_lightbox'
        );
    }

    function install_jquery_lightbox()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        $lightbox = $mapper->find_by_name('lightbox');

        if (!$lightbox)
        {
            $lightbox = new stdClass();
        }

        $lightbox->name = 'lightbox';
        $lightbox->code = "class='ngg_lightbox'";
        $lightbox->css_stylesheets = $this->static_url('/css/jquery.lightbox-0.5.css');
        $lightbox->scripts = implode(
            "\n",
            array(
                $this->static_url('/js/jquery.lightbox-0.5.min.js'),
                $this->static_url('/js/nextgen_lightbox_init.js')
            )
        );
        $lightbox->values = array(
            'nextgen_lightbox_loading_img_url' => $this->static_url('/images/lightbox-ico-loading.gif'),
            'nextgen_lightbox_close_btn_url'   => $this->static_url('/images/lightbox-btn-close.gif'),
            'nextgen_lightbox_btn_prev_url'    => $this->static_url('/images/lightbox-btn-prev.gif'),
            'nextgen_lightbox_btn_next_url'    => $this->static_url('/images/lightbox-btn-next.gif'),
            'nextgen_lightbox_blank_img_url'   => $this->static_url('/images/lightbox-blank.gif')
        );

        $mapper->save($lightbox);
    }

}
