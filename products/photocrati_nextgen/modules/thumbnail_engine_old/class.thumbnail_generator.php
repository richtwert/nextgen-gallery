<?php

class C_Thumbnail_Generator extends C_Component
{
    var $image, $config;
    
    
    function initialize($image, $config, $context=FALSE)
    {
        parent::initialize($context);
        $this->image = $image;
        $this->config = $config;
    }
    
    function has_global_thumbnail($image=FALSE)
    {
        if (!$image) $image = $this->image;
        return !file_exists($image->get_thumbnail_path());
    }
    
    
    function process($global=FALSE)
    {
        $size = array();
        
        // Extract from nextgen-gallery/admin/functions.php | create_thumbnail
        if(! class_exists('ngg_Thumbnail'))
            require_once( nggGallery::graphic_library() );
        
        // Get thumbnail paths
        $thumbnail_path     = $global ? $this->image->get_thumbnail_path() : 
            $this->image->get_thumbnail_path($this->config);
        $thumbnail_filename = $global ? $this->image->get_thumbnail_filename() : 
            $this->image->get_thumbnail_filename($this->config);
        
        // Ensure that the thumbnail directory exists
        if (!file_exists($thumbnail_path) AND !wp_mkdir_p($thumbnail_path)) {
            throw new Exception(_("The thumbnail directory does not exist. Our attempt to create the directory failed. Please check your permissions."));
        }
        
        // Ensure that the thumbnail directory is writable
        if (!is_writable($thumbnail_path) AND !chmod($thumbnail_path, 775)) {
            throw new Exception(_("The thumbnail directory is not writable. Please change the permissions to be 775"));
        }
        
        // Create a stub for the thumbnail
        if (!touch($thumbnail_filename)) {
            throw new Exception(_("Cannot write to ").$thumbnail_filename);
        }
        
        
        // Create the thumbnail
        $thumb = new ngg_Thumbnail($this->image->get_filename(), TRUE);
        if (!$thumb->error) {
            if ($this->config->thumbnail_crop)  {

                // calculate correct ratio
                $wratio = $this->config->thumbnail_width / $thumb->currentDimensions['width'];
                $hratio = $this->config->thumbnail_height / $thumb->currentDimensions['height'];

                if ($wratio > $hratio) {
                    // first resize to the wanted width
                    $thumb->resize($this->config->thumbnail_width, 0);
                    // get optimal y startpos
                    $ypos = ($thumb->currentDimensions['height'] - $this->config->thumbnail_height) / 2;
                    $thumb->crop(0, $ypos, $this->config->thumbnail_width,$this->config->thumbnail_height);	
                } 
                else {
                    // first resize to the wanted height
                    $thumb->resize(0, $this->config->thumbnail_height);	
                    // get optimal x startpos
                    $xpos = ($thumb->currentDimensions['width'] - $this->config->thumbnail_width) / 2;
                    $thumb->crop($xpos, 0, $this->config->thumbnail_width,$this->config->thumbnail_height);	
                }
            }
            
            //this create a thumbnail but keep ratio settings	
            else {
                $thumb->resize($this->config->thumbnail_width,$this->config->thumbnail_height);	
            }

            // save the new thumbnail
            $thumb->save($thumbnail_filename, $this->config->thumbnail_quality);
            if ($thumb->error) throw new Exception($thumb->errmsg);

            //read the new sizes
            $new_size = @getimagesize ( $thumbnail_filename );
            $size['width'] = $new_size[0];
            $size['height'] = $new_size[1]; 
        }
        else throw new Exception(_("An error occurred trying to generate thumbnails: ").$thumb->errmsg);
        
        return $size;
    }
}