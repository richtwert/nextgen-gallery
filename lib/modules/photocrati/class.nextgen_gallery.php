<?php

/**
 * We create this as an extension as it encapsulates methods which will most
 * likely be replaced by adapters.
 *
 * For other than that, we could have just defined these methods in C_NextGen_Gallery
 */
class Mixin_NextGen_Gallery_Persistence extends Mixin
{
    function _create()
    {
        // Here for legacy purposes
        $this->object->name = apply_filters('ngg_gallery_name', $this->object->name);
        
        // Get the default gallery storage path
        $name = $this->object->name;
        $pc_options = $this->object->_registry->get_singleton_utility('I_Photocrati_Options');
        $storage_dir = $pc_options->storage_dir;
        unset($pc_options);
        $gallery_dir = path_join(ABSPATH, path_join($storage_dir, $name));
        
        // Check for existing folder
        if ( is_dir($gallery_dir) ) {
            $suffix = 1;
            do {
                    $alt_name = substr ($name, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "_$suffix";
                    $gallery_dir = path_join(ABSPATH, path_join($storage_dir,$alt_name));
                    $dir_check = is_dir($gallery_dir);
                    $suffix++;
            } while ( $dir_check );
            $name = $alt_name;
        }
        
        // Set gallery dir
        $this->object->path = path_join($storage_dir, $name);
        
        // Call Active Record's create method
        $retval = $this->call_parent();
        
        // here you can inject a custom function. Again for legacy purposes
        do_action('ngg_created_new_gallery', $this->object->id());
        
        return $retval;
        
    }
    
    /**
     * Returns the absolute path to the gallery path 
     */
    function get_gallery_path()
    {
        return path_join(ABSPATH, $this->object->path);
    }
}


class Mixin_NextGen_Gallery_Storage extends Mixin
{
    function import_image($image=array(), $multiple=FALSE)
    {   
        $retval = array();
        if (!$multiple) $image = array($image);
        
        // Ensure that there is enough space first
        include_once(implode(DIRECTORY_SEPARATOR, array(ABSPATH, 'wp-admin', 'includes', 'ms.php')));
        if ( (is_multisite()) && nggWPMU::wpmu_enable_function('wpmuQuotaCheck'))
            
            // For whatever reason, this file is not always available multisite features, so we make sure that 
            // it's loaded
            if( $error = upload_is_user_over_quota( false ) ) {
                $retval = FALSE;
                delete_transient('dirsize_cache');
                throw new Exception(_("Sorry, you have used your space allocation. Please delete some files to upload more files"));
        }
        
        // Retrieve storage directory
        $gallery_dir = path_join(ABSPATH, $this->object->path);
        if (!$gallery_dir) {
            throw new Exception(_("Please save the gallery first before trying to import images"));
        }
        
        // For each image passed, move it to the storage directory and create
        // a corresponding image record
        foreach (array_values($image) as $img) {
            //Array
            //(
            //    [name] => Canada_landscape4.jpg
            //    [type] => image/jpeg
            //    [tmp_name] => /private/var/tmp/php6KO7Dc
            //    [error] => 0
            //    [size] => 64975
            //)
            
            if ($img['error']) {
                throw new Exception(
                    _("There was a problem uploading the image:").
                    isset($img['name'])? $img['name'] : _('unknown filename')
                );
            }
            
            // filter function to rename/change/modify image before
            $image_path = path_join($gallery_dir, str_replace(' ', '_', $img['name']));
            $image_path = apply_filters('ngg_pre_add_new_image', $image_path, $this->object->id());
            
            // Create the image record
            $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory', FALSE, 'imported_image');
            $path_parts = pathinfo( $image_path );
            $alt_text = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
            $gallery_image = $factory->create('gallery_image', array(
               'filename'   => basename($image_path),
               'galleryid'  => $this->object->id(),
               'alttext'    => $alt_text
            ), 'imported_image');
            unset($factory);
            
            // If everything is good...
            $gallery_image->validate();
            if ($gallery_image->is_valid()) {
                
                // Create the storage directory incase it doesn't exist already
                if (!file_exists($gallery_dir)) wp_mkdir_p($gallery_dir);
                
                // Store the image in the gallery directory
                if (!move_uploaded_file($img['tmp_name'], $image_path)) {
                    throw new Exception(_("Could not store the image. Please check directory permissions and try again."));
                }
                
                // Save the image to the database
                $gallery_image->save();
                
                // Notify other plugins that an image has been added
                do_action('ngg_added_new_image', $gallery_image);

                // delete dirsize after adding new images
                delete_transient( 'dirsize_cache' );

                // Seems redundant to above hook. Maintaining for legacy purposes
                do_action(
                    'ngg_after_new_images_added',
                    $this->object->id(), 
                    array($gallery_image->id())
                );
                
                //add the preview image if needed
                // TODO: Using NextGen legacy class. Should provide an instance method
                // that performs this functionality
                include_once(path_join(NGGALLERY_ABSPATH, 'admin/functions.php'));
                nggAdmin::set_gallery_preview ($this->object->id());
            }
            
            // Return the image, with or without errors
            if ($multiple) $retval[] = $gallery_image;
            else $retval = $gallery_image;
        }
        
        return $retval;
    }
}


/**
 * Applies an ActiveRecord interface to working with NextGen Galleries
 */
class C_NextGen_Gallery extends C_Active_Record
{   
    const NAME          = 'name';
    const SLUG          = 'slug';
    const PATH          = 'path';
    const TITLE         = 'title';
    const DESCRIPTION   = 'galdesc';
    const AUTHOR        = 'author';
    const AUTHOR_ID     = 'author';
    const IMAGE_GALLERY_ID = 'galleryid';
    
    
    /**
     * Defines the interfaces and methods (through extensions and hooks)
     * that this class provides
     */
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_NextGen_Gallery_Persistence');
        $this->add_mixin('Mixin_NextGen_Gallery_Storage');
        $this->implement('I_Gallery');
    }
    
    function initialize($metadata=array(), $context=FALSE)
    {
        parent::initialize($metadata, $context);
        $this->table_name = $this->db->get_table_name('galleries');
        $this->id_field = 'gid';
        
    }
    
    /**
     * Validates whether the gallery can be saved
     */
    function validation()
    {
        // Generate new slug
        $this->slug = nggdb::get_unique_slug( sanitize_title($this->title), 'gallery' );
        
        // If author is missing, then set to the current user id
        // TODO: Using wordpress function. Should use abstraction
        if (!$this->author) {
            $this->author = get_current_user_id();
        }
        
        $this->validates_presence_of(self::NAME);
        $this->validates_uniqueness_of(self::SLUG);
        $this->validates_numericality_of(self::AUTHOR_ID);
    }
    
    /*
     * Internal method. Used to get a property value
    **/
    function _get_property($p)
    {
        return $this->$p;
    }
    
    
    /**
     * Returns all images that belong to the gallery
     * @param string $order
     * @param int $start
     * @param int $limit
     * @param string $context
     * @return array 
     */
    function get_images($order='', $start=0, $limit=0, $context=FALSE)
    {
        /**
         * @var $component C_NextGen_Gallery_Image
         */
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $component = $factory->create('gallery_image', $context);
        return $component->find_by(self::IMAGE_GALLERY_ID." = %s", array($this->id()), $order, $start, $limit, $context);
    }
}