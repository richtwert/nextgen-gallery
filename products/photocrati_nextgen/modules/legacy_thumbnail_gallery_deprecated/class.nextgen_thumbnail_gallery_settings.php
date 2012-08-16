<?php

class C_NextGen_Thumbnail_Gallery_Settings extends C_Thumbnail_Settings
{
    var $form_identifier = __CLASS__;
    
    
    function configure_fields()
    {
        $this->append_field(array(
            'id'        =>  'ngg_thumbnails_show_thumbnails_link',
            'name'      => 'show_thumbnails_link',
            'label'     =>  _('Show Thumbnails Link?'),
            'template'  => 'show_thumbnails_link',
            'help'      => 'Display a link to switch back to Thumbnails when displaying the gallery as a slideshow'
        ));
        
        
        $this->append_field(array(
            'name'      => 'thumbnail_link_text',
            'id'        => 'ngg_thumbnails_thumbnail_link_text',
            'label'     => _('Thumbnail Link Text'),
            'template'  => 'thumbnail_link_text',
            'help'      => "The text of the link used to switch back to Thumbnails when displaying the gallery as a slideshow"
        ));
        
        
        $this->append_field(array(
            'id'        => 'ngg_thumbnails_show_slideshow_link',
            'name'      => 'show_slideshow_link',
            'label'     => _("Show Slideshow Link?"),
            'template'  => 'show_slideshow_link'  
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_thumbnails_slideshow_link_text',
            'name'      => 'slideshow_link_text',
            'label'     => _('Slideshow Link Text'),
            'template'  => 'slideshow_link_text'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_thumbnails_show_piclens_link',
            'name'      => 'show_piclens_link',
            'label'     => _("Show PicLens Link?"),
            'template'  => 'show_piclens_link',
            'help'      => _('Display the gallery using the CoolIris PicLens plugin')
        ));
        
        
        $this->append_field(array(
            'id'        => 'ngg_thumbnails_piclens_link_text',
            'label'     => _('PicLens Link Text'),
            'name'      => 'piclens_link_text',
            'template'  => 'piclens_link_text'
        ));
        
        
        $this->append_field(array(
            'id'        => 'ngg_thumbnails_num_of_columns',
            'name'      => 'num_of_columns',
            'label'     => _('Number of columns'),
            'template'  => 'number_of_columns',
            'help'      => _('If greater than 0, images will be displayed in the number of specified columns')
        ));
        
        $this->append_field(array(
            'id'    => 'ngg_thumbnails_images_per_page',
            'name'  => 'images_per_page',
            'label' => _('Number of images per page'),
            'template'  => 'images_per_page',
            'help'  => _('If greater than 0, the number of specified images will appear on the page and require pagination to view remaining images in the gallery')
        ));
    }
    
    function get_config()
    {
        $factory = $this->get_registry()->get_utility('I_Component_Factory');
        return $factory->create(
            'nextgen_thumbnail_gallery_config',
            $this->handle_this_form()? $this->param('settings') : array()
        );
    }
    
    
    function settings_js()
    {
        $this->set_content_type('application/x-javascript');
        $this->render_view('settings_js');
    }
    
    
    function get_gallery_name()
    {
        return _('NextGen Basic Thumbnails');
    }
    
    function custom_display_template()
    {
        $template_dirs = array(
            path_join(STYLESHEETPATH, "nggallery"),
            path_join(NGGALLERY_ABSPATH, 'view')
        );
        
        $this->render_view('custom_display_template', array(
            'template_dirs' => $template_dirs
        ));
    }
}
