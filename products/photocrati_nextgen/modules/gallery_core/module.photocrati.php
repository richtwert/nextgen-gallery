<?php

/***
	{
		Module: photocrati-base,
		Depends: { photocrati-mvc, photocrati-datamapper, photocrati-simple_html_dom, photocrati-fancybox-1x, photocrati-thickbox, photocrati-shutter-reloaded, photocrati-highslide, photocrati-jquery-lightbox }
	}
***/


class Mixin_Load_Lightbox_Library extends Mixin
{
    function load_lightbox_library()
    {
        // Only execute in frontend
        if (is_backend()) return;

        // Create a factory to hatch C_Lightbox_Library objects
        $factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory');

        // Find the default
        $lightbox = $factory->create('lightbox_library');
        $lightbox = $lightbox->find_default();
        if ($lightbox) {
            global $post;
            wp_enqueue_script($lightbox->script);
            if ($lightbox->style) wp_enqueue_style($lightbox->style);
            $post->post_content .= "<script type='text/javascript'>{$lightbox->javascript_code}</script>";
        }
    }
}


class Mixin_Dequeue_NextGen_Legacy_Scripts extends Mixin
{
    /**
     * Removes any queued scripts that the original NextGen legacy plugin
     * provides, as each gallery type should queue using Resource Loader
     * @global type $wp_scripts
     */
    function dequeue_scripts()
    {
        if (!is_admin()) {
            global $wp_scripts;

            $wp_scripts->remove('ngg_slideshow');
            //$wp_scripts->remove('jquery-cycle');
        }
    }
}

class M_Photocrati extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-base',
            'Photocrati Gallery',
            "Provides Photocrati's abstraction for NextGen Gallery",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );

        $this->add_mixin('Mixin_Dequeue_NextGen_Legacy_Scripts');
        $this->add_mixin('Mixin_Load_Lightbox_Library');
    }


    function _register_hooks()
    {
        add_action('wp_print_scripts',  array(&$this, 'dequeue_scripts'));
        //add_action('wp_enqueue_scripts',  array(&$this, 'load_lightbox_library'));
    }


    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Photocrati_Factory');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Parse_Image_Metadata', 'imported_image');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Auto_Rotate_Image', 'imported_image');
        $this->_get_registry()->add_adapter('I_Gallery_Image',     'A_Auto_Resize_Image', 'imported_image');
		$this->_get_registry()->add_adapter('I_CustomPost_DataMapper', 'A_Attachment_DataMapper', 'attachment');
		$this->_get_registry()->add_adapter('I_CustomPost_DataMapper', 'A_CustomPost_Gallery_Mapper_Path', 'gallery');
		$this->_get_registry()->add_adapter('I_CustomTable_DataMapper', 'A_CustomTable_Gallery_Mapper_Path', 'gallery');
    }


    function _register_utilities()
    {
        $this->_get_registry()->add_utility('I_Photocrati_Options','C_Photocrati_Options');
		$this->_get_registry()->add_utility('I_Gallery_Mapper', 'C_Gallery_Mapper');
		$this->_get_registry()->add_utility('I_Gallery_Image_Mapper', 'C_Gallery_Image_Mapper');
    }
}
new M_Photocrati();
