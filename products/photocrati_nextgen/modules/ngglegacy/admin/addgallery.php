<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class nggAddGallery extends C_Component
{
	/**
	 * @type C_Photocrati_Options
	 */
	var $_options = $this->_get_registry()->get_utility('I_Photocrati_Options');

	/**
	 * @type C_Gallery_Storage
	 */
	var $_storage = $this->_get_registry()->get_utility('gallery_storage');

	/**
	 * Initializes the object
	 */
    function initialize($context=FALSE)
	{
		parent::initialize($context);

        // same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	    $this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];

  		//Look for POST updates
		if ( !empty($_POST) )
			$this->processor();
    }


	/**
	 * Gets the component factory
	 * @return C_Component_Factory
	 */
	function _get_factory()
	{
		return $this->_get_registry()->get_singleton_utility('I_Component_Factory');
	}


	/**
	 * Gets the gallery datamapper
	 */
	function _get_gallery_mapper()
	{
		return $this->_get_registry()->get_utility('I_Gallery_Mapper');
	}

	/**
	 * Perform the upload and add a new hook for plugins
	 *
	 * @return void
	 */
	function processor()
	{
    	if ( isset($_POST['addgallery']) ){
    		check_admin_referer('ngg_addgallery');

			$gallery_mapper = $this->_get_registry()->get_utility('I_Gallery_Mapper');
			$gallery = $this->_get_factory()->create('gallery', array(
				'name'	=>	esc_attr($_POST['galleryname'])
			));
			$gallery_mapper->save($gallery);
			if ($gallery->is_invalid()) {
				// TODO: display errors
			}
    	}

    	if ( isset($_POST['zipupload']) ){
    		check_admin_referer('ngg_addgallery');
			if (!$this->_storage->upload_image(intval( $_POST['zipgalselect'])) {
				// TODO: display errors
			}
    	}

    	if ( isset($_POST['importfolder']) ){
    		check_admin_referer('ngg_addgallery');
			if (!$this->_storage->import_folder($_POST['galleryfolder'])) {
				// TODO: display errors
			}
    	}

    	if ( isset($_POST['uploadimage']) ){
    		check_admin_referer('ngg_addgallery');
			if (!$this->_storage->upload_image((int) $_POST['galleryselect'])) {
				// TODO: display errors
			}
    	}

		// TODO: WE'll remove swfuploading and just use Plupload. If we're not
		// using WP 3.3, then we'll include our own Plupload libraries
//    	if ( isset($_POST['swf_callback']) ){
//    		if ($_POST['galleryselect'] == '0' )
//    			nggGallery::show_error(__('No gallery selected !','nggallery'));
//    		else {
//                if ($_POST['swf_callback'] == '-1' )
//                    nggGallery::show_error( __('Upload failed! ','nggallery') );
//                else {
//					$gallery_id = (int) $_POST['galleryselect'];
//					$gallery_path = $this->get_gallery_path($gallery_id);
//                    nggAdmin::import_gallery( $gallery_path );
//                }
//            }
//    	}

		// TODO: Is this still needed given that we don't use swfUpload any more?
    	if ( isset($_POST['disable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$this->_options->swfUpload = FALSE;
			$this->_options->save();
    	}
    	elseif ( isset($_POST['enable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$this->_options->swfUpload = TRUE;
			$this->_options->save();
    	}

        do_action( 'ngg_update_addgallery_page' );

    }

    /**
     * Render the page content
     *
     * @return void
     */
    function controller() {

    	// check for the max image size
    	$this->maxsize    = @ini_get('upload_max_filesize')

    	// get all galleries, newest first
		$mapper = $this->_get_gallery_mapper();
		$this->gallerylist = $mapper->select()->order(
			$mapper->get_primary_key_column(), 'DESC'
		)->run_query();

		// get the upload path
        $this->defaultpath = $this->_storage->get_upload_path();

        // link for the flash file
    	$swf_upload_link = NGGALLERY_URLPATH . 'admin/upload.php';

        // get list of tabs
        $tabs = $this->tabs_order();

        // with this filter you can add custom file types
        $file_types = apply_filters( 'ngg_swf_file_types', '*.jpg;*.jpeg;*.gif;*.png;*.JPG;*.JPEG;*.GIF;*.PNG' );

        // Set the post params, which plupload will post back with the file, and pass them through a filter.
        $post_params = array(
        		"auth_cookie" => (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]),
        		"logged_in_cookie" => $_COOKIE[LOGGED_IN_COOKIE],
        		"_wpnonce" => wp_create_nonce('ngg_swfupload'),
        		"galleryselect" => "0",
        );
        $p = array();

        foreach ( $post_params as $param => $val ) {
        	$val = esc_js( $val );
        	$p[] = "'$param' : '$val'";
        }

        $post_params_str = implode( ',', $p ). "\n";

	?>

	<?php if(!empty ($this->gallerylist) ) { ?>
    <!-- plupload script -->
    <script type="text/javascript">
    //<![CDATA[
    var resize_height = <?php echo (int) $this->_options->imgHeight; ?>,
    	resize_width = <?php echo (int) $this->_options->imgWidth; ?>;

    jQuery(document).ready(function($) {
    	window.uploader = new plupload.Uploader({
    		runtimes: '<?php echo apply_filters('plupload_runtimes', 'html5,flash,silverlight,html4,'); ?>',
    		browse_button: 'plupload-browse-button',
    		container: 'plupload-upload-ui',
    		drop_element: 'uploadimage',
    		file_data_name: 'Filedata',
    		max_file_size: '<?php echo round( (int) wp_max_upload_size() / 1024 ); ?>kb',
    		url: '<?php echo esc_js( $swf_upload_link ); ?>',
    		flash_swf_url: '<?php echo esc_js( includes_url('js/plupload/plupload.flash.swf') ); ?>',
    		silverlight_xap_url: '<?php echo esc_js( includes_url('js/plupload/plupload.silverlight.xap') ); ?>',
    		filters: [
    			{title: '<?php echo esc_js( __('Image Files', 'nggallery') ); ?>', extensions: '<?php echo esc_js( str_replace( array('*.', ';'), array('', ','), $file_types)  ); ?>'}
    		],
    		multipart: true,
    		urlstream_upload: true,
    		multipart_params : {
    			<?php echo $post_params_str; ?>
    		},
            debug: false,
            preinit : {
    			Init: function(up, info) {
    				debug('[Init]', 'Info :', info,  'Features :', up.features);
                    initUploader();
    			}
            },
			i18n : {
				'remove' : '<?php _e('remove', 'nggallery') ;?>',
				'browse' : '<?php _e('Browse...', 'nggallery') ;?>',
				'upload' : '<?php _e('Upload images', 'nggallery') ;?>'
			}
    	});

    	uploader.bind('FilesAdded', function(up, files) {
    		$.each(files, function(i, file) {
    			fileQueued(file);
    		});

    		up.refresh();
    	});

    	uploader.bind('BeforeUpload', function(up, file) {
            uploadStart(file);
    	});

    	uploader.bind('UploadProgress', function(up, file) {
    		uploadProgress(file, file.loaded, file.size);
    	});

    	uploader.bind('Error', function(up, err) {
    		uploadError(err.file, err.code, err.message);

    		up.refresh();
    	});

    	uploader.bind('FileUploaded', function(up, file, response) {
    		uploadSuccess(file, response);
    	});

    	uploader.bind('UploadComplete', function(up, file) {
    		uploadComplete(file);
    	});

		// on load change the upload to plupload
		uploader.init();

		nggAjaxOptions = {
		  	header: "<?php _e('Upload images', 'nggallery') ;?>",
		  	maxStep: 100
		};

    });
    //]]>
    </script>
	<!-- jQuery Tabs script -->
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery(document).ready(function(){
            jQuery('html,body').scrollTop(0);
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });
            jQuery('#slider').css('display', 'block');
		});

		// File Tree implementation
		jQuery(function() {
		    jQuery("span.browsefiles").show().click(function(){
    		    jQuery("#file_browser").fileTree({
    		      script: "admin-ajax.php?action=ngg_file_browser&nonce=<?php echo wp_create_nonce( 'ngg-ajax' ) ;?>",
                  root: jQuery("#galleryfolder").val()
    		    }, function(folder) {
    		        jQuery("#galleryfolder").val( folder );
    		    });
		    	jQuery("#file_browser").show('slide');
		    });
		});
	/* ]]> */
	</script>
	<div id="slider" class="wrap" style="display: none;">
        <ul id="tabs">
            <?php
        	foreach($tabs as $tab_key => $tab_name) {
        	   echo "\n\t\t<li><a href='#$tab_key'>$tab_name</a></li>";
            }
            ?>
		</ul>
        <?php
        foreach($tabs as $tab_key => $tab_name) {
            echo "\n\t<div id='$tab_key'>\n";
            // Looks for the internal class function, otherwise enable a hook for plugins
            if ( method_exists( $this, "tab_$tab_key" ))
                call_user_func( array( &$this , "tab_$tab_key") );
            else
                do_action( 'ngg_tab_content_' . $tab_key );
             echo "\n\t</div>";
        }
        ?>
    </div>
    <?php

    }

    /**
     * Create array for tabs and add a filter for other plugins to inject more tabs
     *
     * @return array $tabs
     */
    function tabs_order() {

    	$tabs = array();

    	if ( !empty ($this->gallerylist) )
    	   $tabs['uploadimage'] = __( 'Upload Images', 'nggallery' );

        if ( nggGallery::current_user_can( 'NextGEN Add new gallery' ))
    	   $tabs['addgallery'] = __('Add new gallery', 'nggallery');

        if ( wpmu_enable_function('wpmuZipUpload') && current_user_can(PHOTOCRATI_GALLERY_UPLOAD_ZIP_CAP) )
            $tabs['zipupload'] = __('Upload a Zip-File', 'nggallery');

        if ( wpmu_enable_function('wpmuImportFolder') && nggGallery::current_user_can( PHOTOCRATI_GALLERY_IMPORT_FOLDER_CAP ) )
            $tabs['importfolder'] = __('Import image folder', 'nggallery');

    	$tabs = apply_filters('ngg_addgallery_tabs', $tabs);

    	return $tabs;

    }

    function tab_addgallery() {
    ?>
		<!-- create gallery -->
		<h2><?php _e('Add new gallery', 'nggallery') ;?></h2>
		<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $this->filepath; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('New Gallery', 'nggallery') ;?>:</th>
				<td><input type="text" size="35" name="galleryname" value="" /><br />
				<i>( <?php _e('Allowed characters for file and folder names are', 'nggallery') ;?>: a-z, A-Z, 0-9, -, _ )</i></td>
			</tr>
			<?php do_action('ngg_add_new_gallery_form'); ?>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "addgallery" value="<?php _e('Add gallery', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_zipupload() {
    ?>
		<!-- zip-file operation -->
		<h2><?php _e('Upload a Zip-File', 'nggallery') ;?></h2>
		<form name="zipupload" id="zipupload_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->filepath.'#zipupload'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Select Zip-File', 'nggallery') ;?>:</th>
				<td><input type="file" name="zipfile" id="zipfile" size="35" class="uploadform"/><br />
				<?php _e('Upload a zip file with images', 'nggallery') ;?></td>
			</tr>
			<?php if (function_exists('curl_init')) : ?>
			<tr valign="top">
				<th scope="row"><?php _e('or enter a Zip-File URL', 'nggallery') ;?>:</th>
				<td><input type="text" name="zipurl" id="zipurl" size="35" class="uploadform"/><br />
				<?php _e('Import a zip file with images from a url', 'nggallery') ;?></td>
			</tr>
			<?php endif; ?>
			<tr valign="top">
				<th scope="row"><?php _e('in to', 'nggallery') ;?></th>
				<td><select name="zipgalselect">
				<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
				<?php
					$gallery_mapper = $this->_get_gallery_mapper();
					$gallery_key = $gallery_mapper->get_primary_key_column();
					foreach($this->gallerylist as $gallery) {
						if (! $gallery_mapper->can_manage_this_manage($gallery->author))
							continue;
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->$gallery_key . '" >' . $gallery->$gallery_key . ' - ' . esc_attr( $name ). '</option>' . "\n";
					}
				?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . echo $maxsize . "Byte</strong>\n"; ?>
				<br /><?php if ( (is_multisite()) && wpmu_enable_function('wpmuQuotaCheck') ) display_space_usage(); ?></td>
			</tr>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "zipupload" value="<?php _e('Start upload', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_importfolder() {
    ?>
	<!-- import folder -->
	<h2><?php _e('Import image folder', 'nggallery') ;?></h2>
		<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $this->filepath.'#importfolder'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Import from Server path:', 'nggallery') ;?></th>
				<td><input type="text" size="35" id="galleryfolder" name="galleryfolder" value="<?php echo $this->defaultpath; ?>" /><span class="browsefiles button" style="display:none"><?php _e('Browse...', 'nggallery'); ?></span><br />
				<div id="file_browser"></div>
				<br /><i>( <?php _e('Note : Change the default path in the gallery settings', 'nggallery') ;?> )</i>
				<br /><?php echo $this->maxsize; ?>
				<?php if (SAFE_MODE) {?><br /><?php _e(' Please note : For safe-mode = ON you need to add the subfolder thumbs manually', 'nggallery') ;?><?php }; ?></td>
			</tr>
			</table>
			<div class="submit"><input class="button-primary" type="submit" name= "importfolder" value="<?php _e('Import folder', 'nggallery') ;?>"/></div>
		</form>
    <?php
    }

    function tab_uploadimage() {
		$img_width	= (int)$this->_options->imgWidth;
		$img_height = (int)$this->_options->imgHeight;

        // check the cookie for the current setting
        $checked = get_user_setting('ngg_upload_resize') ? ' checked="true"' : '';
    ?>
    	<!-- upload images -->
    	<h2><?php _e('Upload Images', 'nggallery') ;?></h2>
		<form name="uploadimage" id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->filepath.'#uploadimage'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Upload image', 'nggallery') ;?></th>
				<td>
                <div id="plupload-upload-ui">
                	<div>
                    	<?php _e( 'Choose files to upload' ); ?>
                    	<input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" />
                	</div>
                	<p class="ngg-dragdrop-info howto" style="display:none;" ><?php _e('Or you can drop the files into this window.'); ?></p>
                    <div id='uploadQueue'></div>
                    <p><label><input name="image_resize" type="checkbox" id="image_resize" value="true"<?php echo $checked; ?> />
                        <?php printf( __( 'Scale images to max width %1$dpx or max height %2$dpx', 'nggallery' ), $img_width, $img_height ); ?>
                        <div id='image_resize_pointer'>&nbsp;</div>
                        </label>
                    </p>

                 </div>
                </td>
            </tr>
			<tr valign="top">
				<th scope="row"><?php _e('in to', 'nggallery') ;?></th>
				<td><select name="galleryselect" id="galleryselect">
				<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
				<?php
					$gallery_mapper = $this->_get_gallery_mapper();
					$gallery_key    = $gallery_mapper->get_primary_key_column();
					foreach($this->gallerylist as $gallery) {

						//special case : we check if a user has this cap, then we override the second cap check
						if ( !current_user_can( PHOTOCRATI_GALLERY_UPLOAD_IMAGE ) )
							if ( !$gallery_mapper->can_manage_this_gallery($gallery->author) )
								continue;

						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->$gallery_key. '" >' . $gallery->$gallery_key . ' - ' . esc_attr( $name ) . '</option>' . "\n";
					}					?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<br /><?php if ((is_multisite()) && wpmu_enable_function('wpmuQuotaCheck')) display_space_usage(); ?></td>
			</tr>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e('Upload images', 'nggallery') ;?>" />
			</div>
		</form>
    <?php
    }
}
?>