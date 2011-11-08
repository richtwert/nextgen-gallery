<?php

/**
 * Provides the frontend view for NextGen Thumbnail Galleries 
 */
class C_NextGen_Thumbnail_Gallery_View extends C_MVC_Controller
{
    function define()
    {
        $this->add_mixin('Mixin_NextGen_Thumbnail_Gallery_View');
    }
    
    
    // Renders the gallery type frontend
    function index()
    {
        // Create factory
        $factory    = $this->_registry->get_singleton_utility('I_Component_Factory');
        $image      = $factory->create('gallery_image');
        $width      = $this->gallery_instance->settings['thumbnail_width'];
        $height     = $this->gallery_instance->settings['thumbnail_height'];
        
        // Collect images
        $images = array();
        foreach ($this->gallery_instance->image as $id => $properties) {
            $img = $image->find($id);
            
            // Override image to use gallery instance properties
            $img->update_properties($properties);
            $thumbnail = $img->get_thumbnail_url(
                (object)$this->gallery_instance->settings
            );
            $img->merge_meta(array('thumbnail' => array('width'=>$width, 'height'=>$height)));
            $img = $img->to_nggImage(); // wrapped version of ngg_image
            $img->thumbURL = $thumbnail;
            
            // Add the image to the list to be rendered
            $images[] = $img;
        }
        
        // Call NextGen legacy methods
        echo nggCreateGallery($images, false, $this->gallery_instance->display_template);
    }
}