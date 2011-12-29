<?php

class C_NextGen_Thumbnail_Gallery_Config extends C_Thumbnail_Config
{
    function initialize($settings = FALSE, $context = FALSE)
    {
        if ($this->is_empty($settings, 'show_slideshow_link'))
            $settings['show_slideshow_link'] = 0;
        
        if ($this->is_empty($settings, 'show_piclens_link'))
            $settings['show_piclens_link'] = 0;
        
        if ($this->is_empty($settings, 'show_thumbnail_link'))
            $settings['show_thumbnail_link'] = 1;
        
        if ($this->is_empty($settings, 'slideshow_link_text'))
            $settings['slideshow_link_text'] = _('Show as slideshow');
        
        if ($this->is_empty($settings, 'thumbnail_link_text'))
            $settings['thumbnail_link_text'] = _('Show as thumbnails');
        
        if ($this->is_empty($settings, 'picliens_link_text'))
            $settings['piclens_link_text'] = _('Show as PicLens');
        
        if ($this->is_empty($settings, 'num_of_columns'))
            $settings['num_of_columns'] = '0';
        
        if ($this->is_empty($settings, 'images_per_page'))
            $settings['images_per_page'] = '0';
        
        if ($this->is_empty($settings, 'thumbnail_quality'))
            $settings['thumbnail_quality'] = 100;
        
        if ($this->is_empty($settings, 'thumbnail_width')) {
            $settings['thumbnail_width'] = 100;
        }
        
        if ($this->is_empty($settings, 'thumbnail_height')) {
            $settings['thumbnail_height'] = 80;
        }
        
        if ($this->is_empty($settings, 'thumbnail_crop')) {
            $settings['thumbnail_crop'] = 1;
        }
        
        parent::initialize($settings, $context);
    }
}
