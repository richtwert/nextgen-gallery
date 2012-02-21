<?php


class Mixin_Replicate_Thumbnail_Config extends Mixin
{
    // Replicates the thumbnail configuration to ngg_options, used by
    // NextGen Legacy
    function save($updates=array())
    {
        if ($this->call_parent()) {
            $ngg_options = get_option('ngg_options');
            $ngg_options['thumbwidth']  = $this->object->thumbnail_width;
            $ngg_options['thumbheight'] = $this->object->thumbnail_height;
            $ngg_options['thumbfix']    = $this->object->thumbnail_crop;
            $ngg_options['thumbquality']= $this->object->thumbnail_quality;
            update_option('ngg_options', $ngg_options);
        }
    }
    
    function set_defaults()
    {
        $this->object->settings = array_merge($this->object->settings, array(
            'use_thumbnails'    =>  1,
            'show_captions'     =>  0,
            'thumbnail_quality' =>  100,
            'thumbnail_crop'    =>  1,
            'thumbnail_width'   =>  100,
            'thumbnail_height'  =>  80
        ));
    }
}


class C_Thumbnail_Config extends C_Base_Component_Config
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Replicate_Thumbnail_Config');
    }
    
    
    function validation()
    {
        $this->validates_presence_of('thumbnail_width');
        $this->validates_presence_of('thumbnail_height');
        $this->Validates_presence_of('thumbnail_quality');
        $this->validates_numericality_of(array('thumbnail_width', 'thumbnail_height', 'thumbnail_quality'));
        
        return !$this->has_errors();
    }
}