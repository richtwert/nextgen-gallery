<?php
/**
{
    Module: photocrati-nextgen_addgallery_page
}
**/

define('NEXTGEN_ADD_GALLERY_SLUG', 'ngg_addgallery');

class M_NextGen_AddGallery_Page extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_addgallery_page',
            'NextGEN Add Gallery Page',
            'Provides admin page for adding a gallery and uploading images',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Page_Manager', 'A_NextGen_AddGallery_Pages');
        $this->get_registry()->add_adapter('I_Form_Manager', 'A_NextGen_AddGallery_Forms');
        $this->get_registry()->add_adapter('I_Ajax_Controller', 'A_NextGen_AddGallery_Ajax');
    }

    function _register_hooks()
    {
        add_action('admin_init', array(&$this, 'register_scripts'));
    }

    function register_scripts()
    {
        $router = $this->_get_registry()->get_utility('I_Router');
        wp_register_script('plupload.queue', $router->get_static_url('nextgen_addgallery_page#plupload_queue/jquery.plupload.queue.js'), array('plupload-all'));
        wp_register_style('plupload.queue', $router->get_static_url('nextgen_addgallery_page#plupload_queue/css/jquery.plupload.queue.css'));
        wp_register_style('nextgen_addgallery_page', $router->get_static_url('nextgen_addgallery_page#styles.css'));
        wp_register_script('jquery.filetree', $router->get_static_url('nextgen_addgallery_page#jquery.filetree/jquery.filetree.js'), array('jquery'));
        wp_register_style('jquery.filetree', $router->get_static_url('nextgen_addgallery_page#jquery.filetree/jquery.filetree.css'));
    }
}
new M_NextGen_AddGallery_Page();