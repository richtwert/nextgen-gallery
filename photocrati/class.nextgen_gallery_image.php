<?php


class Mixin_NextGen_Gallery_Image_Defaults extends Mixin
{
    function set_defaults($properties)
    {
        if ($this->is_empty($properties, 'post_id'))
            $properties['post_id'] = 0;
        
        if ($this->is_empty($properties, 'meta_data'))
            $properties['meta_data'] = array('saved' => FALSE);

        // Unserialize metadata, if it's still in string (serialized) form
        else $this->object->try_unserialize($properties['meta_data']);
        
        return $properties;
    }
}


class Mixin_NextGen_Gallery_Image_Paths
{
    
    /**
     * Returns the filename of the image
     * @return string 
     */
    function get_filename()
    {
        $retval = NULL;
        
        $gallery = $this->object->get_gallery();
        $retval = path_join($gallery->get_gallery_path(), $this->object->filename);
        unset($gallery);
        
        return $retval;
    }
    
    /**
     * Returns the url to the image 
     */
    function get_url()
    {
        return path_join(
            real_site_url(), 
            str_replace(
                ABSPATH,
                '',
                $this->object->get_filename()
            )
        );
    }
    
    
    function to_img_tag()
    {
        $meta = $this->object->try_unserialize($this->object->meta_data);
        $src = h($this->object->get_url());
        $width = h($meta['width']);
        $height = h($meta['height']);
        $title = h($this->object->description);
        $alt = h($this->object->alttext);
        return "<img src='{$src}' width='{$width}' height='{$height}' alt='{$alt}' title='{$title}'/>";
    }
}


class Mixin_NextGen_Gallery_Image_Persistence extends Mixin
{
    function save($updates=array())
    {   
        $this->update_properties($updates);
        $this->object->meta_data = $this->try_serialize($this->object->meta_data);
        $retval = $this->call_parent();
        $this->object->meta_data = $this->try_unserialize($this->object->meta_data);
        return $retval;
    }
}


class Mixin_NextGen_Gallery_Image_Conversion extends Mixin
{
    /**
     * Converts the image to a legacy image class, nggImage 
     */
    function to_nggImage()
    {
        $retval = NULL;
        
        // We actually have a wrapper for nggImage, that makes it behave
        // like any other component. So, we need a factory
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        
        // An nggImage sucks. It expects to be passed a single object
        // which contains both the image and gallery properties set.
        $gallery = $this->object->get_gallery();
        if ($gallery) {
            $p = $this->array_merge_assoc(
                $gallery->properties,
                $this->object->properties
            );
            $p = (object) $p;
            
            // An nggImage also expects the meta_data serialized by default
            $p->meta_data = $this->object->try_serialize($p->meta_data);
            $retval = $factory->create('nggImage', $p);
        }
        
        return $retval;
    }
}


/**
 * Applies the active record pattern to NextGen Gallery Images
 */
class C_NextGen_Gallery_Image extends C_Active_Record
{
    const IMAGE_ID          = 'pid';
    const POST_ID           = 'post_id';
    const GALLERY_ID        = 'galleryid';
    const FILENAME          = 'filename';
    const DESCRIPTION       = 'description';
    const ALTERNATE_TEXT    = 'alttext';
    const IMAGE_DATE        = 'imagedate';
    const EXCLUDE           = 'exclude';
    const SORT_ORDER        = 'sortorde';
    const META_DATA         = 'meta_data';
    
    
    function define()
    {
        parent::define();
        $this->implement('I_Gallery_Image');
        $this->add_mixin('Mixin_NextGen_Gallery_Image_Persistence');
        $this->add_mixin('Mixin_NextGen_Gallery_Image_Paths');
        $this->add_mixin('Mixin_NextGen_Gallery_Image_Conversion');
        $this->add_mixin('Mixin_NextGen_Gallery_Image_Defaults');
    }
    
    function initialize($properties=array(), $context=FALSE)
    {   
        // Continue initialization
        parent::initialize($properties, $context);
        $this->table_name = $this->db->get_table_name('gallery_image');
        $this->object_name = 'gallery_image';
        $this->id_field = self::IMAGE_ID;
    }
    
    
    function validation()
    {
        $this->validates_presence_of(self::GALLERY_ID);
        $this->validates_numericality_of(self::GALLERY_ID);
        $this->validates_numericality_of(self::POST_ID);
        $this->validates_presence_of(self::FILENAME);
    }
    
    
    function get_gallery()
    {
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $gallery = $factory->create('gallery');
        return $gallery->find($this->gallery_id());
    }
    
    
    function gallery_id()
    {
        return $this->__get(self::GALLERY_ID);
    }
    
    function gallery_image_id()
    {
        return $this->id();
    }
    
    /**
     * Respond to legacy properties that other Nextgen classes expect
     * @param string $name
     * @return mixed 
     */
    function __get($name)
    {
        $retval = NULL;
        
        switch($name) {
            case 'imagePath':
                $retval = $this->get_filename();
                break;
            default:
                $retval = &parent::__get($name);
                break;
        }
        
        return $retval;
    }
    
    /**
     * Merges new meta data with existing meta data
     * @param array $meta
     * @return array 
     */
    function merge_meta($meta=array())
    {
        if (is_string($meta)) $meta = $this->try_unserialize ($meta);
        if (is_array($meta)) {
            $meta = $this->array_merge_assoc(
                $this->try_unserialize($this->__get('meta_data')),
                $meta
            );
            $this->__set('meta_data', $meta);
        }
        return $this->__get('meta_data');
    }
}