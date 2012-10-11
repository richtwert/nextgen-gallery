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
     * Initializes the module
     */
    function initialize()
    {
        parent::initialize();
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
        // add_action('widgets_init', create_function('', 'return register_widget("nggMediaRssWidget");'));
        // add_action('widgets_init', create_function('', 'return register_widget("nggWidget");'));
        // add_action('widgets_init', create_function('', 'return register_widget("nggSlideshowWidget");'));
    }

}

new M_Widget();
