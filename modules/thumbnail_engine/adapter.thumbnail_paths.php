<?php

/**
 *Overrides the get_thumbnail_path function to include  
 */
class A_Thumbnail_Paths extends Mixin
{
    function get_thumbnail_path($config=FALSE)
    {
        $retval = NULL;
        
        // Get the base thumbnail path
        $gallery = $this->object->get_gallery();
        if ($gallery) {
            $retval =  path_join($gallery->get_gallery_path(), 'thumbs');
            unset($gallery);
            
            // If a thumbnail configuration has been given,
            // then return the path specific to that configuration
            if ($config) {
                if (is_array($config)) $config = (object) $config;
                
                // A gallery instance might have been given, but we still have
                // to check if thumbnail settings are part of it
                if (isset($config->generate_thumbnails)) 
                    $retval = path_join($retval, $key = hash('md5', photocrati_gallery_plugin_serialize($config)));
            }
        }
        
        return $retval;
    }
    
    /**
     * Returns the filename of the thumbnail
     * @return type 
     */
    function get_thumbnail_filename($config=FALSE)
    {
        $retval = '';
        
        $retval = path_join(
            $this->object->get_thumbnail_path($config),
            'thumbs_'.basename($this->object->get_filename())
        );

        return $retval;
    }
    
    
    /**
     * Gets the url for the thumbnail
     * @return string 
     */
    function get_thumbnail_url($config=FALSE)
    {
        $retval = '';
        
        $retval = path_join(
             real_site_url(),
             str_replace(ABSPATH, '', $this->object->get_thumbnail_filename($config))
        );
        
        return $retval;
    }
    
    
    function to_thumbnail_img_tag($config=FALSE)
    {
       $src = h($this->object->get_thumbnail_url($config));
       if ($config) {
           if (is_array($config)) $config = (object) $config;
       }else {
           // Get thumbnail configuration
            $factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory');
            $config = $factory->create('thumbnail_config');
       }
       $alt = h($this->object->alttext);
       $title = h($this->object->description);
       $width = h($config->thumbnail_width);
       $height = h($config->thumbnail_height);
       return "<img
           src='{$src}'
           alt='{$alt}'
           title='{$title}'
           width='{$width}'
           height='{$height}'
           ref='{$this->object->get_url()}'
         />"; 
    }
}
