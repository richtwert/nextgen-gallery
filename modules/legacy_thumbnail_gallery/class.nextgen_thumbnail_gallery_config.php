<?php

class C_NextGen_Thumbnail_Gallery_Config extends C_Thumbnail_Config
{   
    function set_defaults()
    {
        $this->settings = array_merge($this->settings, array(
            'thumbnail_crop'        =>  1,
            'thumbnail_height'      =>  80,
            'thumbnail_width'       =>  100,
            'thumbnail_quality'     =>  100,
            'images_per_page'       =>  0,
            'num_of_columns'        =>  0,
            'piclens_link_text'     =>  _('Show as PicLens'),
            'thumbnail_link_text'   =>  _('Show as Thumbnails'),
            'slideshow_link_text'   =>  _('Show as Slideshow'),
            'show_thumbnail_link'   =>  1,
            'show_slideshow_link'   =>  0,
        ));
        parent::set_defaults();
    }
}
