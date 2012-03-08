<?php

class Mixin_Attached_Gallery_Queries extends Mixin
{
    function find($id)
    {   
        $retval = NULL;
        
        // Collect custom post type fields
        $properties = array();
        $custom = (array) get_post($id);
        if ($custom && isset($custom['post_content'])) {
            
            // Ensure that properties is unserialized
            $arr = $custom['post_content'];
            if (is_string($arr) && strpos($arr, "a:") !== FALSE) {
                $arr = $this->object->try_unserialize($arr);
            }
            
            // Get each property
            foreach ($arr as $key => $value) {

                // If the value is serialized, then we need to unserialize it
                if (is_string($value) && strpos($value, "a:") !== FALSE) {
                    $value = $this->object->try_unserialize($value);
                }

                // Assign the property
                $properties[$key] = $value;
            }
            $properties['attached_gallery_id'] = $id;
            $properties['ID'] = $id;
            $properties['post_id'] = $id;
        }
        else {
            $properties['attached_gallery_id'] = FALSE;
        }
        
        // Get the config for this gallery type
        $config = $this->object->factory->create(
            'gallery_type_config',
            $properties['gallery_type']
        );

        // Create merged set of settings for the attached gallery.
        // We do this so that we can include any new default values into
        // the attached gallery
        $settings = $config->settings;
        foreach ($properties['settings'] as $key => $value) {
            $settings[$key] = $value;
        }
        $properties['settings'] = $settings;
        
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
            $properties['post_title'] = $this->object->gallery_name;
            $properties['post_content'] = $this->object->try_serialize($this->object->properties);
            $properties['post_excerpt'] = $this->object->gallery_description;

            // Create post
            if (($id = wp_insert_post($properties))) {
                $retval = $id;
                $this->object->__set('ID', $retval);
                $this->object->__set('attached_gallery_id', $retval);
                $this->object->__Set('post_id', $retval);
            }
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
    
    
    function get_gallery_type_css_class()
    {
        return strtolower(
            preg_replace(
                "/[^A-Za-z0-9]+/", 
                '_',
                $this->object->__get('gallery_type')
            )
        );
    }
    
    function get_images($page=FALSE, $num_per_page=FALSE, $legacy=FALSE, $include_exclusions=FALSE, $context=FALSE)
    {
        $source = $this->object->properties['gallery_source'];
        $find_list = null;
        $images = array();
        
        if (in_array($source, array('recent_images', 'random_images'))) {
        	$legacy = false;
					$factory = $this->_registry->get_singleton_utility('I_Component_Factory');
					$component = $factory->create('gallery_image');
					$total = 10;
					$gal_total = 2; // XXX Not implemented 
					$only_attached = true; // XXX Not implemented 
			
					//$images = $component->find_by(C_NextGen_Gallery_Image::IMAGE_DATE . " = %s", array($this->id()), '', $start, $num_per_page, $context);
					$find_list = $component->find_by('', array(), C_NextGen_Gallery_Image::IMAGE_ID, 0, $total);
        }
        else {
		      // Needed to create images
		      $image_factory = $this->object->factory->create('attached_gallery_image');
		      
		      $find_list = $image_factory->find_by('attached_gallery_id', $this->object->id(), $page, $num_per_page, $include_exclusions, $context);
        }
        
	      foreach ($find_list as $gallery_image) {
            
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
