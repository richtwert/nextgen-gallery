<?php

class Mixin_Attach_To_Post_Preview_Image extends Mixin
{
    function preview($return=FALSE)
    {
        $filename = $this->find_static_file('blank.gif');
        
        // If we're editing an existing gallery instance, populate it
        if ($this->param('attached_gallery_id')) {
            $this->object->_get_attached_gallery(
                $this->param('attached_gallery_id')
            );
            
            $image = $this->attached_gallery->get_images(1,1);
            if ($image && ($image = array_shift($image))) {
                $filename = $image->get_thumbnail_filename($this->attached_gallery->settings);
            }
        }
        
        // Render!
        $content_type = filetype($filename);
        header('Content-Type: image/gif');
        header('Content-Length: '.filesize($filename));
        readfile($filename);
    }
}



class Mixin_Attach_To_Post_Ajax extends Mixin
{
    function _save_attached_gallery()
    {
        $retval = array();
        
        try {
            $params = $this->object->_params;
            $params['ID'] = $params['attached_gallery_id'];
            
            // Create attached gallery with the parameters
            $attached_gallery = $this->object->factory->create(
                'attached_gallery',
                $params
            );
            
            // We'll get the gallery config object
            $gallery_config = $this->object->factory->create(
                'gallery_type_config',
                $this->object->param('gallery_type'),
                $this->object->param('settings')
            );
            
            // Validate the gallery settings config. If it's valid,
            // then we can create an attached gallery.
            $gallery_config->validate();
            if ($gallery_config->is_valid()) {
                
                // If the attached gallery is valid, then we can create
                // images for the attached gallery
                
                if (!(($retval['saved'] = $attached_gallery->is_valid()))) {
                    $retval['validation_errors'] = $attached_gallery->get_errors();
                }
                    
                // Create the attached gallery images
                else {
                    $attached_gallery->save();
                    
                    // Ensure that we have images
                    $images = $this->object->param('images');
                    if ($images) {
                        foreach ($images as $order => $overrides) {
                            $attached_gallery_image = FALSE;

                            // Is this an existing attached gallery image id ?
                            if (isset($overrides['attached_gallery_image_id']) && $overrides['attached_gallery_image_id']) {
                                $image_factory = $this->object->factory->create('attached_gallery_image');
                                $attached_gallery_image = $image_factory->find($overrides['attached_gallery_image_id']);
                                unset($image_factory);
                            }

                            // We need to create a new attached gallery image, based
                            // on an existing gallery image
                            elseif(isset($overrides['gallery_image_id']) && $overrides['gallery_image_id']) {
                                $gallery_image = $this->object->factory->create('gallery_image');
                                $gallery_image = $gallery_image->find($overrides['gallery_image_id']);
                                $overrides['attached_gallery_id'] = $attached_gallery->id();
                                $overrides = $this->array_merge_assoc($gallery_image->properties, $overrides);
                                $attached_gallery_image = $this->object->factory->create('attached_gallery_image', $overrides);
                            }

                            // If we have an image and it saved successfully
                            if ($attached_gallery_image) {
                                if  ($attached_gallery_image->save($overrides)) {


                                    // Return the new image id
                                    if (!isset($retval['attached_gallery_image_ids'])) {
                                        $retval['attached_gallery_image_ids'] = array();
                                    }
                                    $retval['attached_gallery_image_ids'][] = $attached_gallery_image->id();
                                }    

                                // The image was not saved successfully. Return the validation errors
                                else {
                                    if (!isset($retval['image_validation_errors'])) {
                                        $retval['image_validation_errors'] = array();
                                    }
                                    $retval['image_validation_errors'][] = $attached_gallery_image->get_errors();
                                }
                            }
                            else $retval['error'] = _("Gallery image does NOT exist");
                        }                        
                        // Include the new attached gallery id in the response
                        $retval['attached_gallery_id'] = $attached_gallery->id();
                    }
                    else {
                        $retval['error'] = _("No images selected. You must at least choose one image to display.");
                        $attached_gallery->delete();
                    }
                    
                }
            }
            
            // The gallery settings are not valid
            else {
                $retval['gallery_setting_validation_errors'] = $gallery_config->get_errors();
            }
        }
        catch (Exception $ex) {
            $retval['error'] = $ex->getMessage();
            $retval['stack'] = $ex->getTraceAsString();
        }        
        
        // Perform any clean up if errors occured
        foreach (array('error','image_validation_errors') as $field) {
            if (isset($retval[$field])) {
                
                // TODO: When an attached gallery is deleted, the attached
                // gallery images should be deleted as well
                $attached_gallery->delete();
                $retval['saved'] = FALSE;
                unset($retval['attached_gallery_id']);
            }
        }
        
        return $retval;
    }
    
    
    /**
     * Validates an image before saving
     * @return array
     */
    function _validate_image()
    {
        $reval = array();
        
        if (($image_id = $this->object->param('image_id'))) {
            
            // Get gallery image and apply attached gallery image overrides
            $gallery_image = $this->object->factory->create('gallery_image');
            $gallery_image = $gallery_image->find($image_id);
            $overrides = $this->array_merge_assoc($gallery_image->properties, $this->object->_params);
            
            // Verify that the overrides are valid
            $gallery_image->validate();
            if ($gallery_image->is_valid()) $retval['success'] = TRUE;
            else $retval['validation_errors'] = $gallery_image->get_errors();
        }
        else {
            $retval['error'] = "Missing image id";
        }
        
        return $retval;
    }
    
    /**
     * Gets the gallery display settings form for the specified
     * gallery type.
     */
    function _get_gallery_display_settings_form()
    {
        $retval = array();
        
        if (($gallery_type = $this->param('gallery_type'))) {
            $controller = $this->object->_instantiate_gallery_settings_controller($gallery_type);
            
            $retval['html'] = $controller->index(TRUE);
        }
        else {
            $retval['error'] = _('No gallery type selected');
        }
        
        return $retval;
    }
    
    
    /**
     * Params:
     * - Filename
     * - name
     * - action
     * - [gallery instance properties: gallery_id, gallery_name, etc]
     */
    function _upload_image()
    {
        $retval = array();
        
        // the gallery object
        $gallery = FALSE;
        
        // Is this an existing gallery?
        if ($this->param('gallery_source') == 'existing_gallery') {
            $gallery = $this->object->factory->create('gallery');
            $gallery = $gallery->find($this->param('gallery_id'));
        }
        
        // We need to create the gallery first
        else {
            $gallery = $this->object->factory->create('gallery', array(
                'name'      => $this->param('gallery_name'),
                'galdesc'   => $this->param('gallery_description')
            ));
            $gallery->save();
            
        }
        
        // If the image is valid and saved, then we'll import the image
        if ($gallery && $gallery->is_valid() && !$gallery->is_new()) {
            try {
                $retval['gallery_id'] = $gallery->id();
                $retval['gallery_name'] = $gallery->name;
                $retval['gallery_description'] = $gallery->galdesc;
                $image = $gallery->import_image($_FILES['file']);
                if (!$image->is_valid()) {
                    $retval['validation_errors'] = $this->object->show_errors_for($image, TRUE);
                }
                else {
                    $retval['image'] = $image->properties;
                    $retval['image']['image_id'] = $image->id();
                    $retval['image']['gallery_id'] = $gallery->id();
                }
            }
            catch (Exception $ex) {
                $retval['stack'] = $ex->getTraceAsString();
                $retval['error'] = $ex->getMessage();
            }
        }
        else {
            $retval['validation_errors'] = $this->object->show_errors_for($gallery, TRUE);
        }
        
        return $retval;
            
    }
    
    /**
     * Gets images for an attached gallery or gallery
     */
    function _get_image_forms()
    {
        $retval = array();
        
        if (($source = $this->object->param('source')) && ($id = $this->object->param('id'))) {
            $forms = array();
            
            // Create source object
            $obj = $this->object->factory->create($source);
            $obj = $obj->find($id);
            
            // Create arguments for get_images() call
            $args = array(
                $this->object->param('page', FALSE),
                $this->object->param('num_per_age', FALSE),
                FALSE, // legacy 
            );
            
            // Include exclusions for attached galleries
            if ($source == 'attached_gallery') $args[] = TRUE;
            
            // Include a context
            $args[] = 'attach_to_post';
            
            // Get image forms
            $order = 1;
            foreach ($obj->call_method('get_images', $args) as $image) {
                
                // Rendering an attached gallery image
                if ($source == 'attached_gallery') {
                    $forms[] = $this->object->render_image_form(
                        $image,
                        $image->gallery_image_id,
                        $image->id(),
                        $order
                    );
                }
                
                // Rendering a gallery image
                else {
                    $image->included = TRUE;
                    $forms[] = $this->object->render_image_form(
                        $image,
                        $image->id(),
                        '',
                        $order
                    );
                }
                
                $order++;
            }
        
            // Render the image options tab
            $retval['html'] = $this->render_partial('image_options_tab', array(
               'images' =>  $forms 
            ), TRUE);
        }
        
        return $retval;
    }
    
    function ajax()
    {
        $retval = array('error' => 'Action does not exist');
        
        if ($this->param('action') && $this->has_method($this->param('action'))) {
            $action = $this->param('action');
            unset($this->object->_params[$action]);
            $retval = $this->$action();
        }
        
        // Needed by CGI
        header('Content-Type: application/json');
        flush();
        
        // Output the JSON
        echo json_encode($retval);
    }
}

class Mixin_Attach_To_Post_Resources extends Mixin
{
    /**
     * Renders scripts and styles used in the attach to post interface
     */
    function _render_scripts_and_styles()
    {
        
        wp_register_style(
            'jquery.plupload.queue',
            $this->static_url('plupload/jquery.plupload.queue/css/jquery.plupload.queue.css'
        ));
        wp_register_script(
            'browserplus',
            'http://bp.yahooapis.com/2.4.21/browserplus-min.js'
        );
        
        wp_register_script(
            'form2js',
            $this->static_url('form2js.js')
        );
        
        wp_deregister_script('plupload');
        wp_register_script(
            'plupload',
            $this->static_url('plupload/plupload.full.js'),
            array('jquery', 'browserplus'),
            '1.5.2'
        );
        wp_register_script(
           'jquery.plupload.queue',
           $this->static_url('plupload/jquery.plupload.queue/jquery.plupload.queue.js'),
           array('plupload')
        );
        wp_register_style(
            'nextgen_attach_to_post', 
            $this->static_url('styles.css')
        );
        wp_register_script(
            'nextgen_attach_to_post', 
            $this->static_url('attach_to_post.js'),
            array('jquery.plupload.queue', 'form2js', 'jquery-ui-core')
        );
        wp_enqueue_style('global');
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('colors-fresh');
        wp_enqueue_script('tiptip');
        wp_enqueue_style('tiptip');
        wp_enqueue_style('jquery.plupload.queue');
        wp_enqueue_style('nextgen_attach_to_post');
        wp_enqueue_script('nextgen_attach_to_post');
        wp_localize_script(
            'nextgen_attach_to_post',
            'vars',
            $this->object->get_js_vars()
        );
        do_action('admin_print_styles');
        do_action('admin_head');
        do_action('admin_print_scripts');
    }
    
    
    /**
     * Gets vars to be included in the Attach To Post script
     * @return array 
     */
    function get_js_vars()
    {
        return array(
            'ajax_url'          => PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_AJAX_URL,
            'max_file_size'     => @ini_get('upload_max_filesize') ? 
                strtolower(ini_get('upload_max_filesize').'b') : '50mb',
            'plupload_swf_url'  => $this->static_url('plupload/plupload.flash.swf'),
            'plupload_xap_url'  => $this->static_url('plupload/plupload.silverlight.xap')
        );
    }
}


class Mixin_Attach_To_Post_Gallery_Types extends Mixin
{
    function render_gallery_type_tab()
    {
        // Populate gallery type previews
        $gallery_types = array();
        foreach (C_Gallery_Type_Registry::get_all() as $gallery_type => $properties) {
            $controller = $this->object->_instantiate_gallery_settings_controller($gallery_type);
            $gallery_types[$gallery_type] = $controller->preview(TRUE);
        }
        
        // Render the gallery types
        return $this->render_partial('gallery_type_tab', array(
            'gallery_types'         =>  $gallery_types,
            'selected_gallery_type' =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_type'
                                        )
        ), TRUE);
    }
}



class Mixin_Attach_To_Post_Gallery_Display extends Mixin
{
    function render_gallery_display_tab()
    {
        $retval = '';
        
        // If a gallery type has been selected
        if (($gallery_type = $this->object->_get_value($this->object->attached_gallery,'gallery_type'))) {
            $controller = $this->object->_instantiate_gallery_settings_controller($gallery_type);
            $retval = $controller->index(TRUE);
            
        }
        
        else {
            $retval = $this->render_partial('no_gallery_type_selected', array(), TRUE);
        }
        
        return $retval;
    }
}


class Mixin_Attach_To_Post_Image_Options extends Mixin
{
    /**
     * Renders the image options tab
     * @return string 
     */
    function render_image_options_tab()
    {
        $retval = '';
        
        // If a gallery instance is being viewed, then we get the gallery images
        // associated with it
        if (!$this->object->attached_gallery->is_new()) {
            $images = array();
            
            $order=0;
            foreach ($this->object->attached_gallery->get_images(1, 20, FALSE, TRUE) as $image) {
              $images[] = $this->object->render_image_form($image, $image->gallery_image_id, $image->id(), $order);  
              $order++;
            }
            
            $retval = $this->render_partial('image_options_tab', 
                array('images'=>$images), TRUE
            );
        }
        else {
            $retval = $this->render_partial('no_images_available', array(), TRUE);
        }
        
        return $retval;
    }
    
    
    /**
     * Returns the callbacks for the image fields to be rendered per image
     * @return array 
     */
    function get_image_fields()
    {
        return array(
            'caption'       =>  'render_caption_field',
            'description'   =>  'render_description_field',
        );
    }
    
    
    /**
     * Renders a form for a single image
     * @param C_Gallery_Image $gallery_image 
     */
    function render_image_form($image, $gallery_image_id='', $attached_gallery_image_id='', $order=0)
    {
        $fields = array();
        foreach ($this->object->get_image_fields() as $field_id => $callback) {
            if ($this->object->has_method($callback)) {
                $args = func_get_args();
                $fields[] = $this->object->call_method($callback, $args);
            }
        }
        
        return $this->render_partial('image_form', array(
            'image'                     => $image,
            'gallery_image_id'          => $gallery_image_id,
            'attached_gallery_image_id' => $attached_gallery_image_id,
            'fields'                    => $fields,
            'order'                     => $order
        ), TRUE);
    }
    
    
    function render_caption_field($image, $gallery_image_id, $attached_gallery_image_id, $order)
    {
        return $this->render_partial('caption_field', array('image'=>$image, 'order' => $order), TRUE);
    }
    
    
    function render_description_field($image, $gallery_image_id, $attached_gallery_image_id, $order)
    {
        return $this->render_partial('description_field', array('image'=>$image, 'order' => $order), TRUE);
    }
}


class Mixin_Attach_To_Post_Gallery_Sources extends Mixin
{
    /**
     * Renders the gallery source tab
     */
    function render_gallery_source_tab()
    {   
        // Populate sources
        $sources = array();
        $source_views = array();
        foreach ($this->get_gallery_sources() as $source_id => $properties) {
            if ($source_id && isset($properties['callback']) && 
                    $this->object->has_method($properties['callback'])) {
                $source_views[] = $this->object->call_method($properties['callback']);
                $sources[$source_id] = $properties['label'];
            }
        }
        
        return $this->render_partial('gallery_source_tab', array(
            'sources'       =>  $sources,
            'source_views'  =>  $source_views,
            'gallery_source'=>  $this->object->_get_value(
                                    $this->object->attached_gallery,
                                    'gallery_source'
                                ),
        ), TRUE);
    }
    
    
    /**
     * An associative array of gallery sources. Extensions are expected
     * to override this
     * @return array 
     */
    function get_gallery_sources()
    {
        return array(
            'new_gallery'       => array(
                                    'label'     => _('New Gallery'),
                                    'callback'  => 'render_new_gallery_fields'
                                ),
            'existing_gallery'   => array(
                                    'label'     => _('Existing Gallery'),
                                    'callback'  => 'render_existing_gallery_fields'
                                ),
            'new_album'         => array(
                                    'label'     => _('New Album'),
                                    'callback'  => 'render_new_album_fields'
                                ),
            'existing_album'    => array(
                                    'label'     => _('Existing Album'),
                                    'callback'  => 'render_existing_album_fields'
                                ),
            'random_images'     => array(
                                    'label'     => _('Random Gallery Images'),
                                    'callback'  =>  'render_random_images_fields'
                                ),
            'recent_images'     => array(
                                    'label'     => _('Recent Gallery images'),
                                    'callback'  =>  'render_recent_images_fields'
                                )
        );
    }
    
    
    /**
     * Returns the fields needed for the new gallery source
     * @return string 
     */
    function render_new_gallery_fields()
    {
        return $this->render_partial('new_gallery_source', array(
            'gallery_name'          =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_name'    
                                        ),
            'gallery_description'   =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_desc'
                                        ),
        ), TRUE);
    }
    
    
    function render_existing_gallery_fields()
    {
        $gallery_search = $this->object->factory->create('gallery');
        $galleries = $gallery_search->find_all();
        
        return $this->render_partial('existing_gallery_source', array(
           'selected_gallery_id'        =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_id'
                                        ),
           'galleries'                  =>  $galleries
        ), TRUE);
    }
    
    function render_recent_images_fields()
    {
    
    }
}


class Mixin_Attach_To_Post_Tabs extends Mixin
{
    
    /**
     * Produces a list of tabs to be rendered for the Attach To Post interface
     * Other modules are expected to override this or provide hooks to add/remove
     * tabs
     * @return array 
     */
    function get_tabs()
    {
        return array(
          # heading => content
          _('Gallery Source')   
          => 'render_gallery_source_tab',
            
          _('Gallery Type')
          => 'render_gallery_type_tab',
            
          _('Import (Optional)')
          => 'render_import_tab',
            
          _('Gallery Display (Optional - Post Specific)')
          => 'render_gallery_display_tab',
            
          _('Image Options (Optional - Post Specific)')
          => 'render_image_options_tab'
        );
    }
}

class C_Attach_to_Post extends C_Base_Admin_Controller
{
    var $attached_gallery = NULL;
    var $post_id = '';
    var $factory = NULL;
    
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Attach_To_Post_Tabs');
        $this->add_mixin('Mixin_Attach_To_Post_Gallery_Sources');
        $this->add_mixin('Mixin_Attach_To_Post_Gallery_Types');
        $this->add_mixin('Mixin_Attach_To_Post_Gallery_Display');
        $this->add_mixin('Mixin_Attach_To_Post_Image_Options');
        
        $this->add_mixin('Mixin_Attach_To_Post_Preview_Image');
        $this->add_mixin('Mixin_Attach_To_Post_Resources');
        $this->add_mixin('Mixin_Attach_To_Post_Ajax');
    }
    
    
    function initialize($context = FALSE)
    {
        parent::initialize($context);
        $this->factory = $this->_registry->get_singleton_utility('I_Component_Factory');
    }
    
    
    function index()
    {
        // If we're editing an existing gallery instance, populate it
        $this->_get_attached_gallery(
            $this->param('attached_gallery_id', FALSE)
        );
        
        // Ensure we know what post we're on
        $this->post_id = $this->param('post_id');
        
        
        // Process the tabs
        $tabs = array();
        foreach ($this->get_tabs() as $heading => $callback) {
            if ($heading && $callback && $this->has_method($callback))
                $tabs[$heading] = $this->call_method($callback);
        }
        
        // Render the accordion
        $accordion = $this->_render_accordion($tabs, TRUE);
        
        // Render the index view
        $this->render_view('index', array(
           'accordion'              =>  $accordion,
           'attached_gallery_id'    =>  $this->attached_gallery->id(),
           'post_id'                =>  $this->post_id
        ));
    }
    
    
    
    function _get_attached_gallery($id)
    {
        $attached_gallery = $this->factory->create('attached_gallery');
        $this->attached_gallery = $id ?  $attached_gallery->find($id) : $attached_gallery;
        
        return $this->attached_gallery;
    }
    
    
    /**
     * Returns the value of a key or property of an array or object
     * @param mixed $arr_or_object
     * @param type string
     * @param mixed $default
     * @return mixed
     */
    function _get_value($arr_or_object, $key, $default='')
    {
        $retval = $default;
        
        if (is_array($arr_or_object)) {
            if (isset($arr_or_object[$key])) $retval = $arr_or_object[$key];
        }
        elseif (is_object($arr_or_object)) {
            $retval = $arr_or_object->$key;
        }
        
        return $retval;
    }
    
    
    /**
     * Instantiates a gallery settings controller for a particular gallery type
     * @param string $gallery_type
     * @return C_MVC_Controller 
     */
    function _instantiate_gallery_settings_controller($gallery_type)
    {   
        $controller = $this->factory->create(
            'gallery_type_controller',
            $gallery_type,
            TRUE,
            'attach_gallery_customize_settings'
        );
        
        // Override config with attached gallery
        if ($this->attached_gallery) $controller->config = $this->attached_gallery;
        
        return $controller;
    }
}
