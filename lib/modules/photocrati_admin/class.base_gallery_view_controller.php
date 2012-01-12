<?php

class Mixin_Base_Gallery_View_Static_Resources extends Mixin
{   
    function index()
    {
        echo "<p>";
        echo_h(_("No view defined for this gallery type"));
        echo "</p>";
    }
}


class Mixin_Base_Gallery_View_Lightbox extends Mixin
{
    function get_lightbox_html()
    {
        $retval = '';
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $lightbox = $factory->create('lightbox_library');
        $lightbox = $lightbox->find_default();
        if ($lightbox) {
            // Ensure we have the gallery name
            if ($this->is_empty($this->gallery_instance->gallery_name)) {
                $gallery = $factory->create('gallery');
                $gallery = $gallery->find($this->gallery_instance->gallery_id);
                $this->gallery_instance->gallery_name = $gallery->name;
                $this->gallery_instance->gallery_description = $gallery->galdesc;
            }
            
            // Substitute the gallery name placeholder
            $retval = str_replace(
                "%GALLERY_NAME%",
                $this->gallery_instance->gallery_name,
                $lightbox->html
            );        
        }
        
        unset($factory);
        unset($lightbox);
        
        return $retval;
    }
}

/**
 * Renders the frontend view of the gallery 
 */
class C_Base_Gallery_View_Controller extends C_MVC_Controller
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Base_Gallery_View_Static_Resources');
        $this->add_mixin('Mixin_Base_Gallery_View_Lightbox');
    }
    
    function initialize($context=FALSE)
    {
        parent::initialize();
        $this->resource_loader = $this->_registry->get_utility('I_Resource_Loader');
    }
}