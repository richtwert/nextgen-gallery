<?php

class A_Attached_Gallery_Dimensions extends Hook
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save',
            get_class(), 
            get_class(),
            'calculate_dimensions'
        );
    }
    
    
    function calculate_dimensions()
    {
        if ($this->object->has_errors() or !isset($this->object->image)) return;
        
        $longest = 0;
        $widest = 0;
        $average_width = 0;
        $average_height = 0;
        $count = 0;
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        $gallery_image = $factory->create('gallery_image');
        
        foreach ($this->object->image as $id => $properties) {
            $gallery_image = $gallery_image->find($id);
            $meta_data = $this->object->try_unserialize(
                $gallery_image->meta_data
            );
            $count ++;
            $average_width += $meta_data['width'];
            $average_height += $meta_data['height'];
            
            // Is this the widest image?
            if ($meta_data['width'] > $widest) {
                $widest = $meta_data['width'];
            }
            
            // Is this the longest image?
            if ($meta_data['height'] >$longest) {
                $longest = $meta_data['height'];
            } 
        }
        
        // Calculate averages
        $average_height = $average_height/$count;
        $average_width  = $average_width/$count;
        
        // Update gallery instance properties
        $this->object->longest_image = $longest;
        $this->object->widest_image  = $widest;
        $this->object->average_width = $average_width;
        $this->object->average_height = $average_height;
        $this->call_anchor();
    }
}