<?php

/**
 * Provides the frontend view for NextGen Thumbnail Galleries 
 */
class C_NextGen_Thumbnail_Gallery_View extends C_Base_Gallery_View_Controller
{      
    function enqueue_scripts()
    {
        $this->resource_loader->enqueue_script(
            'nextgen_thumbnail_gallery'
        );
    }
    
    function enqueue_stylesheets()
    {
        $this->resource_loader->enqueue_stylesheet(
            'nextgen_thumbnail_gallery'
        );
    }
    
    function index()
    {          
        echo nggShowGallery(
            $this->config,
            $this->config->display_template,
            $this->config->images_per_page,
            $this->attached_gallery_settings_to_ngg_legacy($this->config->settings)
        );
    }
    
    /**
     * Convert the gallery instance settings to the names of legacy settings
     * @param array $settings
     * @return array 
     */
    function attached_gallery_settings_to_ngg_legacy($settings)
    {
        return array(
            'galShowSlide'      =>  $settings['show_slideshow_link'],
            'galTextSlide'      =>  $settings['slideshow_link_text'],
            'galColumns'        =>  $settings['num_of_columns'],
            'usePicLens'        =>  $settings['show_piclens_link'],
            'galImages'         =>  $settings['images_per_page'],
            'galTextGallery'    =>  $settings['thumbnail_link_text'],
            'piclens_link_text' =>  $settings['piclens_link_text']
            
        );
    }
}