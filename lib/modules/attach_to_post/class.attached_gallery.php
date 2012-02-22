<?php

class Mixin_Attached_Gallery_Queries extends Mixin
{
    function find($id)
    {   
        $retval = NULL;
        
        // Collect custom post type fields
        $properties = array();
        $custom = get_post_custom($id);
        if ($custom && isset($custom['properties'])) {
            
            // Ensure that properties is unserialized
            $arr = array_shift($custom['properties']);
            if (is_string($arr) && strpos($arr, "a:") !== FALSE) {
                $arr = unserialize($arr);
            }
            
            // Get each property
            foreach ($arr as $key => $value) {

                // If the value is serialized, then we need to unserialize it
                if (is_string($value) && strpos($value, "a:") !== FALSE) {
                    $value = unserialize($value);
                }

                // Assign the property
                $properties[$key] = $value;
            }
            $properties['attached_gallery_id'] = $id;
        }
        else {
            $properties['attached_gallery_id'] = FALSE;
        }
        
        // Create new attached gallery objected based on the above
        $retval =  $this->object->factory->create(
            $this->object->object_name,
            $properties
        );
        
        return $retval;
    }
}


class Mixin_Attached_Gallery_Persistence extends Mixin
{
    function validation()
    {
        $this->object->validates_presence_of('post_id');
        $this->object->validates_presence_of('gallery_type');
    }
    
    function save($updates=array())
    {
        $retval = FALSE;
        $images = array();
        
        // Update properties if provided
        if ($updates) $this->update_properties($updates);
        
        // Ensure that nothing is overriding the post type
        $this->__set('post_type', 'attached_gallery');
        
        // Validate the object
        $this->object->validate();
        
        if (($retval = $this->object->is_valid())) {
        
            // Images are not stored in the custom post type
            if (isset($this->object->properties['images'])) {
                unset($this->object->properties['images']);
            }
            
            // Are we to create a new record?
            if (!$this->object->id()) {
                
                // Temporarily set some fake properties needed to by-pass the
                // wp_insert_post function limitation: http://core.trac.wordpress.org/ticket/18891
                // For users that don't have WordPress 3.3.1
                $properties = $this->object->properties;
                $properties['post_title'] = $this->object->gallery_name;
                $properties['post_content'] = $this->object->gallery_description;
                $properties['post_excerpt'] = $this->object->gallery_description;
                
                // Create post
                if (($id = wp_insert_post($properties))) {
                    $retval = $id;
                    $this->object->__set('ID', $retval);
                    $this->object->__set('attached_gallery_id', $retval);
                }
            }
            
            // Store the properties as meta data for the post
            update_post_meta(
                $this->object->id(),
                'properties',
                $this->object->properties
            );
            
            
        }
        
        return $retval;
    }
    
    
    function update_properties($updates=array())
    {
        if ($this->object->has_method('set_defaults')) {
            $updates = $this->object->set_defaults($updates);
        }
        
        $this->object->properties = $this->array_merge_assoc(
            $this->properties, $updates
        );
    }
    
    
    function delete()
    {
        return wp_delete_post($this->object->id());
    }
}


class Mixin_Attached_Gallery_Methods extends Mixin
{
    function id()
    {
        return $this->object->__get('ID');
    }
    
    
    function get_gallery()
    {
        $gallery = $this->object->factory->create('gallery');
        return $gallery->find($this->object->__get('gallery_id'));
    }
    
    function get_gallery_type()
    {
        return C_Gallery_Type_Registry::get($this->object->__get('gallery_type'));
    }
    
    function get_images($page=FALSE, $num_per_page=FALSE, $legacy=FALSE, $include_exclusions=FALSE, $context=FALSE)
    {
        $images = array();
        
        // Needed to create images
        $image_factory      = $this->object->factory->create('attached_gallery_image');
        foreach ($image_factory->find_by('attached_gallery_id', $this->object->id(), $page, $num_per_page, $context) as $gallery_image) {
            if (!$include_exclusions && !$gallery_image->included) continue;
            
            // Override image to use gallery instance properties
            $thumbnail = $gallery_image->get_thumbnail_url(
                (object)$this->settings
            );

            // A gallery instance might have it's own specific thumbnail settings
            if (isset($this->object->settings['thumbnail_width'])) {
                $gallery_image->merge_meta(array(
                    'thumbnail' => array(
                            'width' =>$this->object->settings['thumbnail_width'], 
                            'height'=>$this->object->settings['thumbnail_height']
                        )
                    )
                );
            };
            
            // Add image to array, optionally for legacy purposes
            $images[] = $legacy ? $gallery_image->to_nggImage() : $gallery_image;
        }
        
        return $images;
    }    
}

class C_Attached_Gallery extends C_Active_Record
{
    var $factory = NULL;
    
    function define()
    {
        parent::define();
        $this->remove_mixin('Mixin_Active_Record_Persistence');
        $this->add_mixin('Mixin_Attached_Gallery_Methods');
        $this->add_mixin('Mixin_Attached_Gallery_Persistence');
        $this->add_mixin('Mixin_Attached_Gallery_Queries');
    }
    
    function initialize($metadata = array(), $context = FALSE)
    {
        if (isset($metadata['attached_gallery_id'])) $metadata['ID'] = $metadata['attached_gallery_id'];
        
        parent::initialize($metadata, $context);
        $this->table_name = $this->db->get_table_name('posts');
        $this->id_field = 'ID';
        $this->factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $this->object_name = 'attached_gallery';
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