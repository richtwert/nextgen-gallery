<?php
/***
{
        Module: photocrati-widget
}
***/
class M_Widget extends C_Base_Module
{
    /**
     * Defines the module name & version
     */
    function define()
    {
        parent::define(
            'photocrati-widget',
            'Widget',
            'Handles clearing of NextGen Widgets',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    /**
     * Register utilities
     */
    function _register_utilities()
    {
        $this->get_registry()->add_utility('I_Widget', 'C_Widget');
    }

    /**
     * Register hooks
     */
    function _register_hooks()
    {
         add_action('widgets_init', create_function('', 'return register_widget("C_Widget_Gallery");'));
         add_action('widgets_init', create_function('', 'return register_widget("C_Widget_MediaRSS");'));
         add_action('widgets_init', create_function('', 'return register_widget("C_Widget_Slideshow");'));
    }

    function set_file_list()
    {
        return array(
            'class.widget.php',
            'class.widget_gallery.php',
            'class.widget_mediarss.php',
            'class.widget_slideshow.php',
            'interface.widget.php'
        );
    }

}

new M_Widget();
