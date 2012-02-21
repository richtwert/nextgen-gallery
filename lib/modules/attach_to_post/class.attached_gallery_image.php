<?php

class Mixin_Attached_Gallery_Image_Persistence extends Mixin
{
    function save($updates=array())
    {
        $retval = FALSE;
        
        // Update the properties and validate
        $this->update_properties($updates);;
        
        // Ensure that nothing is overriding the post type
        $this->__set('post_type', 'attached_gal_image');
        
        // If it's valid, then persist as a custom post type
        $this->validate();
        
        if ($this->object->is_valid()) {
            
            // Are we to create a new post?
            if (!$this->object->id()) {
            
                // Temporarily set some fake properties needed to by-pass the
                // wp_insert_post function limitation: http://core.trac.wordpress.org/ticket/18891
                // For users that don't have WordPress 3.3.1
                $properties = $this->object->properties;
                $properties['post_title'] = $this->object->alttext;
                $properties['post_content'] = $this->object->alttext;
                $properties['post_excerpt'] = $this->object->alttext;

                if (($retval = wp_insert_post($properties))) {

                    // Set commonly used IDs
                    $this->object->__set('ID', $retval);
                    $this->object->__set('post_id', $retval);
                    $this->object->__set('attached_gallery_image_id', $retval);
                    $this->object->__set('attached_gal_image_id', $retval);
                    $retval = TRUE;
                }
            }
                            
            // Get real meta data
            $properties = $this->object->properties;
            
            // Save the attached gallery id as meta data
            update_post_meta($this->object->id(), 'attached_gallery_id', $this->object->attached_gallery_id);

            // Save the properties as meta data
            update_post_meta($this->object->id(), 'properties', $properties);

            // Save the order
            update_post_meta($this->object->id(), 'order', $properties['order']);
            
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
        $properties = array();
        foreach ($custom as $meta_key => $meta_value) {
            $property = unserialize($meta_value[0]);
            if (is_array($property)) {
                $properties = array_merge($properties, $property);
            }
            else {
                $properties[$meta_key] = $property;
            }
        }
        $retval = $this->object->factory->create('attached_gallery_image', $properties);
        
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

        // Create SQL query
        $sql = $wpdb->prepare("
            SELECT {$wpdb->posts}.*,
                   order_postmeta.meta_value AS `order`,
                   properties_postmeta.meta_value AS `properties`
            FROM {$wpdb->posts} 
            LEFT JOIN {$wpdb->postmeta} order_postmeta ON {$wpdb->posts}.ID = order_postmeta.post_id
            LEFT JOIN {$wpdb->postmeta} properties_postmeta ON {$wpdb->posts}.ID = properties_postmeta.post_id
            WHERE {$wpdb->posts}.ID IN
                (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d)
            AND order_postmeta.meta_key = 'order' AND properties_postmeta.meta_key = 'properties'
            ORDER BY CAST(`order` AS UNSIGNED)                 
        ", $meta_key, $id);
            
        // Iterate through results
        foreach($wpdb->get_results($sql, ARRAY_A) as $post) {
            $properties = unserialize($post['properties']);;
            $properties['meta_data'] = unserialize($properties['meta_data']);
            unset($post['properties']);
            $post = array_merge($post, $properties);
            $post['post_id'] = $post['ID'];
            $results[] = $factory->create('attached_gallery_image', $post);
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
    var $factory = NULL;
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Attached_Gallery_Image_Persistence');
        $this->add_mixin('Mixin_Attached_Gallery_Image_Query');
    }
    
    function initialize($properties = array(), $context = FALSE)
    {
        parent::initialize($properties, $context);
        $this->factory = $this->_registry->get_singleton_utility('I_Component_Factory');
    }
  
    
    function id()
    {
        return $this->__get('ID');
    }
}