<?php

class Mixin_Gallery_Instance_Persistence extends Mixin
{
    function _create()
    {
        $this->object->meta_key = uniqid(TRUE);
        
        return update_post_meta($this->object->post_id, $this->object->meta_key, $this->_get_meta_value());
    }
    
    function _update()
    {   
        return update_post_meta($this->object->post_id, $this->object->meta_key, $this->_get_meta_value());
    }
    
    
    function delete()
    {
        return delete_post_meta_by_key($this->object->meta_key);
    }
    
    function _get_meta_value()
    {
        $properties = $this->object->properties;
        unset($properties['meta_id']);
        unset($properties['post_id']);
        unset($properties['meta_key']);
        unset($properties['meta_value']);
        return $properties;
    }
}


class C_Gallery_Instance extends C_Active_Record
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Gallery_Instance_Persistence');
        $this->implement('I_Gallery_Instance');
    }
    
    
    function initialize($properties=array(), $context=FALSE)
    {   
        parent::initialize($properties, $context);
        $this->table_name = $this->db->get_table_name('postmeta');
        $this->object_name = 'gallery_instance';
        $this->id_field = 'meta_key';
        
        // For each key/value in meta value, set as an Active Record property
        $meta_value = $this->__get('meta_value');
        if ($meta_value) {
            $meta_value = $this->try_unserialize($meta_value);
            foreach ($meta_value as $key => $val) $this->__set($key, $val);
        }
    }
    
    
    function validation()
    {
        $this->validates_presence_of("post_id", NULL, "Post/page not selected");
        $this->validates_presence_of("gallery_type", NULL, "Gallery type not selected");
        $this->validates_presence_of("gallery_id", NULL, "Gallery not selected");
    }
    
    
    function get_gallery()
    {
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $gallery = $factory->create('gallery');
        $gallery = $gallery->find($this->__get('gallery_id'));
        unset($factory);
        return $gallery;
    }
    
    
    function get_gallery_type()
    {
        return C_Gallery_Type_Registry::get($this->__get('gallery_type'));
    }
    
    function get_images($legacy=FALSE)
    {
        $images = array();
        
        // Needed to create images
        $factory    = $this->_registry->get_singleton_utility('I_Component_Factory');
        $image      = $factory->create('gallery_image');
        
        foreach ($this->image as $id => $properties) {
            
            // Skip image if it's not to be displayed
            if (!isset($properties['included'])) {
                continue;
            }
            
            // Fetch image
            $img = $image->find($id);
            
            // Override image to use gallery instance properties
            $img->update_properties($properties);
            $thumbnail = $img->get_thumbnail_url(
                (object)$this->settings
            );
            
            // A gallery instance might have it's own specific thumbnail settings
            if (isset($this->settings['thumbnail_width'])) {
                $img->merge_meta(array(
                    'thumbnail' => array(
                            'width' =>$this->settings['thumbnail_width'], 
                            'height'=>$this->settings['thumbnail_height']
                        )
                    )
                );
            }
                
            
            // If this is to be used for legacy purposes, return nggImage
            // instances.
            if ($legacy) {
                $img = $img->to_nggImage(); // wrapped version of ngg_image
                $img->thumbURL = $thumbnail;
                $img->gallery_instance = $this;
            }
            
            // Add the image to the list to be rendered
            $images[] = $img;
        }
        
        return $images;
    }
    
    /**
     * Gets a property or setting from the gallery instance
     * @param string $property 
     */
    function &__get($property)
    {
        $retval = &parent::__get($property);
        if (is_null($retval)) {
            $settings = parent::__get('settings');
            if (isset($settings[$property])) $retval = &$settings[$property];
        }
        
        return $retval;
    }
}