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
        
        if (($retval = $this->object->is_valid())) {
            // Temporarily set some fake properties needed to by-pass the
            // wp_insert_post function limitation: http://core.trac.wordpress.org/ticket/18891
            // For users that don't have WordPress 3.3.1
            //
            // We can store our properties in one of three ways:
            // 1) Store each property as a single postmeta entry
            // 2) Store properties as a single serialized postmeta entry
            // 3) Store properties as a serialized value in post_content
            //
            // We've opted option #3 for efficent querying capabilities
            $properties = $this->object->properties;
            $properties['post_title'] = $this->object->alttext;
            $properties['post_content'] = serialize($this->object->properties);
            $properties['post_excerpt'] = $this->object->alttext;

            if (($id = wp_insert_post($properties))) {
                $retval = $id;

                // Set commonly used IDs
                $this->object->__set('ID', $retval);
                $this->object->__set('post_id', $retval);
                $this->object->__set('attached_gallery_image_id', $retval);
                $this->object->__set('attached_gal_image_id', $retval);
            }
                            
            // Get real meta data
            $properties = $this->object->properties;
            
            // Save the attached gallery id as meta data
            update_post_meta($this->object->id(), 'attached_gallery_id', $this->object->attached_gallery_id);

            // Save the order
            update_post_meta($this->object->id(), 'order', $properties['order']);
            
            // Save the included/exclusion flag
            update_post_meta($this->object->id(), 'included', $properties['included']);
            
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
        
        // Get properties
        $custom = get_post_custom($id);
        $post = (array)get_post($id);
        if ($post && isset($post['post_content'])) {
            $custom['properties'] = array($post['post_content']);
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

            // Ensure that IDs are set
            $properties['ID'] = $post['ID'];
            $properties['post_id'] = $post['ID'];
            $properties['attached_gallery_image_id'] = $post['ID'];

            $retval = $this->object->factory->create('attached_gallery_image', $properties);
        }
        
        
        return $retval;
    }
    
    function find_by($meta_key, $id, $page=FALSE, $num_per_page=FALSE, $include_excluded=FALSE, $context=FALSE)
    {   
        global $wpdb;
        $results = array();
        
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
                   included_postmeta.meta_value AS `included`,
                   {$wpdb->posts}.post_content AS `properties`
            FROM {$wpdb->posts} 
            LEFT JOIN {$wpdb->postmeta} order_postmeta ON {$wpdb->posts}.ID = order_postmeta.post_id
            LEFT JOIN {$wpdb->postmeta} included_postmeta ON {$wpdb->posts}.ID = included_postmeta.post_id
            WHERE {$wpdb->posts}.ID IN
                (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d)
            AND order_postmeta.meta_key = 'order' AND included_postmeta.meta_key = 'included'                 
        ", $meta_key, $id);
                
        // Add where clause exclude excluded images
        if (!$include_excluded) $sql .= " AND included_postmeta.meta_value = '1'";
                
        // Add ordering
        $sql .= ' ORDER BY CAST(`order` AS UNSIGNED)';
        
        // Add limits
        if ($num_per_page) $sql .= $wpdb->prepare(" LIMIT %d", $num_per_page);
        if ($num_per_page && $page)  $sql .= $wpdb->prepare(" OFFSET %d", $page);        
            
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