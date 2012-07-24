<?php

class Mixin_Attach_To_Post_Preview_Image extends Mixin
{
	/**
	 * Gets the preview image used by the placeholder
	 * @param type $return
	 */
    function preview($return=FALSE)
    {
		// TODO: This method needs to be refactored to actually
		// check the database for the previewpic field of the gallery entity
		// and use that instead of the first image in the gallery
        $filename = $this->find_static_file('blank.gif');

        // If we're editing an existing gallery instance, populate it
        if ($this->param('attached_gallery_id')) {
            $this->object->_get_attached_gallery(
                $this->param('attached_gallery_id')
            );

            // Get the first image of the attached gallery
			$image = $this->attached_gallery->get_first_image();

			// Get the thumbnail filename
			$filename = $this->_get_storage()->get_thumbnail_path($image);
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
            $attached_gallery = $this->object->_get_factory()->create(
                'attached_gallery',
                $params
            );

            // We'll get the gallery config object
            $gallery_config = $this->object->_get_factory()->create(
                'gallery_type_config',
                $this->object->param('gallery_type'),
                $this->object->param('settings')
            );

            // Validate the gallery settings config. If it's valid,
            // then we can create an attached gallery.
            $gallery_config->validate();
            if ($gallery_config->is_valid()) {

                if (!(($retval['saved'] = $attached_gallery->is_valid()))) {
                    $retval['validation_errors'] = $attached_gallery->get_errors();
                }

                // Create the attached gallery images
                else {
                    $attached_gallery->save();

                    // Ensure that we have images
                    $source = $this->object->param('gallery_source');
                    $images = $this->object->param('images');

                    if ($images || in_array($source, array('recent_images', 'random_images'))) {
                        if ($images) {
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

            // Get gallery image and set new properties
			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
            $gallery_image = $mapper->find($image_id);
			unset($mapper);
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

		// Get the gallery mapper
		$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');

        // the gallery object
        $gallery = FALSE;

        // Is this an existing gallery?
        if ($this->param('gallery_source') == 'existing_gallery') {
            $gallery = $mapper->find($this->param('gallery_id'), TRUE);
        }

        // We need to create the gallery first
        else {
            $gallery = $this->object->_get_factory()->create('gallery', $mapper, array(
                'title'      => $this->param('gallery_name'),
                'galdesc'   => $this->param('gallery_description')
            ));
            $gallery->save();
        }

		// No longer require the mapper
		unset($mapper);

        // If the image is valid and saved, then we'll import the image
        if ($gallery && $gallery->is_valid() && !$gallery->is_new()) {
            try {
                $retval['gallery_id'] = $gallery->id();
                $retval['gallery_name'] = $gallery->title;
                $retval['gallery_description'] = $gallery->galdesc;
				$image = $this->object->_get_storage()->upload_image($gallery->id());
                if ($image->is_invalid()) {
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
     $source = $this->object->param('source');
     $id = $this->object->param('id');
		$limit = $this->object->param('limit');
		$offset = $this->object->param('offset');
     $forms = array();

		// Ensure we have the attached gallery or gallery id
		if ($id) {

			// Get random/recent images
			if (in_array($source, array('recent_images', 'random_images'))) {

			}

			// Get images for a gallery
			else {
				// Ensure we've got the gallery id
				$gallery_id = $id;
				$attached_gallery = NULL;
				if ($source == 'attached_gallery') {
					$attached_gallery = $this->object->_get_attached_gallery($id);
					$gallery_id = $attached_gallery->gallery_id;
				}

				// Get the images for the gallery
				$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
				$mapper->select()->where(array("galleryid = %s", $gallery_id));
				$mapper->order_by('sortorder');
				if ($limit) $mapper->limit($limit, $offset);
				foreach ($mapper->run_query() as $image) {
					$forms[] = $this->object->render_image_form($image, $attached_gallery);
				}
			}
		}

     if ($forms) {
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

        wp_register_script(
            'browserplus',
            'http://bp.yahooapis.com/2.4.21/browserplus-min.js'
        );

        wp_register_script(
            'form2js',
            $this->static_url('form2js.js')
        );

        wp_register_script(
           'jquery.plupload.queue',
           $this->static_url('jquery.plupload.queue/jquery.plupload.queue.js'),
           array('plupload')
        );

		wp_register_style(
			'jquery.plupload.queue',
			$this->static_url('jquery.plupload.queue/css/jquery.plupload.queue.css')
		);

        wp_register_style(
            'nextgen_attach_to_post',
            $this->static_url('styles.css', 'plupload')
        );

        wp_register_script(
            'nextgen_attach_to_post',
            $this->static_url('attach_to_post.js'),
            array('jquery.plupload.queue', 'form2js', 'jquery-ui-accordion', 'plupload-all')
        );
        wp_enqueue_style('global');
        wp_enqueue_style('wp-admin');
		wp_enqueue_script('jquery');
		wp_enqueue_style('jquery-ui-smoothness');
        wp_enqueue_style('colors-fresh');
        wp_enqueue_script('tiptip');
        wp_enqueue_style('tiptip');
		wp_enqueue_script('browserplus');
        wp_enqueue_style('jquery.plupload.queue');
        wp_enqueue_style('nextgen_attach_to_post');
        wp_enqueue_script('nextgen_attach_to_post');
        wp_localize_script(
            'nextgen_attach_to_post',
            'nextgen_attach_settings',
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
            'ajax_url'          => $this->object->_get_router()->routing_uri('attach_to_post/ajax'),
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
        if ($this->object->attached_gallery && !$this->object->attached_gallery->is_new()) {
            $images = array();

			$image_mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_MApper');
			$image_mapper->select()->where(array("galleryid = %s", $this->object->attached_gallery->gallery_id));
			foreach ($image_mapper->limit(1,20)->order_by('sortorder')->run_query() as $image) {
              $images[] = $this->object->render_image_form($image, $this->object->attached_gallery);
            }
			unset($image_mapper);

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
    function render_image_form($image)
    {
        $fields = array();
        foreach ($this->object->get_image_fields() as $field_id => $callback) {
            if ($this->object->has_method($callback)) {
                $fields[] = $this->object->call_method($callback, array($image));
            }
        }

		// Is the image included in the attached gallery ?
		$included = TRUE;
		if ($this->attached_gallery) {
			$included = in_array($image->to_model()->id(), $this->attached_gallery->images);
		}

        return $this->render_partial('image_form', array(
            'image'                     => $image,
            'fields'                    => $fields,
			'included'					=> $included,
			'order'						=> $image->sortorder,
        ), TRUE);
    }


    function render_caption_field($image)
    {
        return $this->render_partial('caption_field', array('image'=>$image, 'order' => $image->sortorder), TRUE);
    }


    function render_description_field($image)
    {
        return $this->render_partial('description_field', array('image'=>$image, 'order' => $image->sortorder), TRUE);
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
		$gallery_mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');
		$gallery_key	= $gallery_mapper->get_primary_key_column();
		$galleries		= $gallery_mapper->find_all();
		unset($gallery_mapper);

        return $this->render_partial('existing_gallery_source', array(
			'selected_gallery_id'        =>  $this->object->_get_value(
												$this->object->attached_gallery,
												'gallery_id'
                                        ),
			'galleries'                  =>  $galleries,
			'gallery_key'				 =>	 $gallery_key
        ), TRUE);
    }

    function render_random_images_fields()
    {
        return $this->render_partial('random_images_gallery_source', array(
            'gallery_random_image_total'          =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_random_image_total'
                                        ),
        ), TRUE);
    }

    function render_recent_images_fields()
    {
        return $this->render_partial('recent_images_gallery_source', array(
            'gallery_recent_image_total'          =>  $this->object->_get_value(
                                            $this->object->attached_gallery,
                                            'gallery_recent_image_total'
                                        ),
        ), TRUE);
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
    }


	/**
	 * Gets the gallery storage instance
	 * @return C_Gallery_Storage
	 */
	function _get_storage()
	{
		return $this->_get_registry()->get_utility('I_Gallery_Storage');
	}

	/**
	 * Returns an instance of the component factory
	 * @return C_Component_Factory
	 */
	function _get_factory()
	{
		return $this->_get_registry()->get_singleton_utility('I_Component_Factory');
	}


	function _get_router()
	{
		return $this->_get_registry()->get_singleton_utility('I_Router');
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
           'attached_gallery_id'    =>  $this->attached_gallery ? $this->attached_gallery->id() : FALSE,
           'post_id'                =>  $this->post_id
        ));
    }


	/**
	 * Gets the attached gallery
	 * @param int $id
	 * @return C_Attached_Gallery
	 */
    function _get_attached_gallery($id)
    {
		if ($id) {
			$mapper = $this->object->_get_registry()->get_utility('I_Attached_Gallery_Mapper');
			$this->attached_gallery = $mapper->find($id, TRUE);
			unset($mapper);
		}

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
        $controller = $this->_get_factory()->create(
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
