<?php

class Mixin_Attached_Gallery_Image_Persistence extends Mixin
{
    function save($updates=array())
    {
        $retval = FALSE;
        
        // Update the properties and validate
        $this->update_properties($updates);
        
        // Ensure that nothing is overriding the post type
        $this->__set('post_type', 'attached_gal_image');
        
        // If it's valid, then persist as a custom post type
        $this->validate();
        if ($this->object->is_valid()) {
            if (($retval = wp_insert_post($this->object->properties))) {
                $this->object->__set('ID', $retval);
                
                // Save the attached gallery id as meta data
                update_post_meta($retval, 'attached_gallery_id', $this->object->attached_gallery_id);
                
                // Save the properties as meta data
                update_post_meta($retval, 'properties', $this->object->properties);
            }
        }
        
        return $retval;
    }
    
    function validation()
    {
        parent::validation();
//        $this->validates_presence_of('attached_gallery_id');
        $this->validates_presence_of('post_type');
    }
}


class Mixin_Attached_Gallery_Image_Query extends Mixin 
{
    function find($id, $context=FALSE)
    {
        $retval = NULL;
        
        $custom = get_post_custom($id);
        if ($custom && isset($custom['properties'])) {
            $retval = $this->object->factory->create('attached_gallery_image', $custom[0], $context);
        }
        
        return $retval;
    }
    
    
    function find_by($meta_key, $id, $page=FALSE, $num_per_page=-1, $context=FALSE)
    {   
        $results = array();
        global $wpdb;
        
        // Create factory need to hatch images
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        
//        $query = new WP_Query("post_type=any");
//        $query->post_type = 'attached_gal_image';
//        $query->meta_key = $meta_key;
//        $query->meta_value = $id;
//        $query->posts_per_page = $num_per_page;
//        $query->paged = $page;
        
        $sql = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s)", $meta_key, $id);
        foreach($wpdb->get_results($sql, ARRAY_A) as $post) {
            $sql = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'properties' AND post_id = {$post['ID']}";
            $properties = unserialize($wpdb->get_var($sql));
            $properties = $this->array_merge_assoc($post, $properties);
            $properties['meta_data'] = unserialize($properties['meta_data']);
            $properties['ID'] = $properties['post_id'] = $post['ID'];
            $results[] = $factory->create('attached_gallery_image', $properties);
        }
        
        return $results;
    }
    
    function find_all()
    {
        throw new Exception('Not Implemented');
    }
}


class C_Attached_Gallery_Image extends C_NextGen_Gallery_Image
{   
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Attached_Gallery_Image_Persistence');
        $this->add_mixin('Mixin_Attached_Gallery_Image_Query');
    }
  
    
    function id()
    {
        return $this->__get('ID');
    }
}