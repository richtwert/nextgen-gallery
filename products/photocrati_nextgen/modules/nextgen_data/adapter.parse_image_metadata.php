<?php

class A_Parse_Image_Metadata extends Hook
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save', 
            'parse_image_metadata', 
            get_class($this),
            'parse_image_metadata'
        );
    }
    
    /**
     * Extracted from admin/functions.php 
     */
    function parse_image_metadata()
    {
        die(var_dump($this->object));
        if (!$this->object->is_valid()) return;
        
        //TODO: NextGen provides notices when looking for metadata, as it does
        //not check whether array indexes exist, such as EXIF['title']
//        $er = error_reporting(error_reporting() ^ E_WARNING ^ E_NOTICE);
        $er = error_reporting(0);
        $meta = nggAdmin::import_MetaData($this->object->id());
        error_reporting($er);
        if ($meta) {
            $this->object->merge_meta($meta);
            $this->call_anchor();
        }
    }
}