<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class nggAddGallery {

    /**
     * PHP4 compatibility layer for calling the PHP5 constructor.
     *
     */
    function nggAddGallery() {
        return $this->__construct();
    }

    /**
     * nggAddGallery::__construct()
     *
     * @return void
     */
    function __construct() {

       	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	   $this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];

  		//Look for POST updates
		if ( !empty($_POST) )
			$this->processor();
    }

	/**
	 * Perform the upload and add a new hook for plugins
	 *
	 * @return void
	 */
	function processor() {
        global $wpdb, $ngg, $nggdb;

    	$defaultpath = $ngg->options['gallerypath'];

    	if ( isset($_POST['addgallery']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Add new gallery' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		$newgallery = esc_attr( $_POST['galleryname']);
    		if ( !empty($newgallery) )
    			nggAdmin::create_gallery($newgallery, $defaultpath);
    	}

		if ( isset($_POST['zipupload']) && wpmu_enable_function('wpmuZipUpload') ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Upload a zip' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		if ($_FILES['zipfile']['error'] == 0 || (!empty($_POST['zipurl'])))
    			nggAdmin::import_zipfile( intval( $_POST['zipgalselect'] ) );
    		else
    			nggGallery::show_error( __('Upload failed!','nggallery') );
    	}

    	if ( isset($_POST['importfolder']) && wpmu_enable_function('wpmuImportFolder') ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Import image folder' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		$galleryfolder = $_POST['galleryfolder'];
    		if ( ( !empty($galleryfolder) ) AND ($defaultpath != $galleryfolder) )
    			nggAdmin::import_gallery($galleryfolder);
    	}

    	if ( isset($_POST['uploadimage']) ){
    		check_admin_referer('ngg_addgallery');

    		if ( !nggGallery::current_user_can( 'NextGEN Upload in all galleries' ))
    			wp_die(__('Cheatin&#8217; uh?'));

    		if ( $_FILES['imagefiles']['error'][0] == 0 )
    			$messagetext = nggAdmin::upload_images();
    		else
    			nggGallery::show_error( __('Upload failed! ' . nggAdmin::decode_upload_error( $_FILES['imagefiles']['error'][0]),'nggallery') );
    	}

    	if ( isset($_POST['swf_callback']) ){
    		if ($_POST['galleryselect'] == '0' )
    			nggGallery::show_error(__('No gallery selected !','nggallery'));
    		else {
                if ($_POST['swf_callback'] == '-1' )
                    nggGallery::show_error( __('Upload failed! ','nggallery') );
                else {
                    $gallery = $nggdb->find_gallery( (int) $_POST['galleryselect'] );
                    nggAdmin::import_gallery( $gallery->path );
                }
            }
    	}

    	if ( isset($_POST['disable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$ngg->options['swfUpload'] = false;
    		update_option('ngg_options', $ngg->options);
    	}

    	if ( isset($_POST['enable_flash']) ){
    		check_admin_referer('ngg_addgallery');
    		$ngg->options['swfUpload'] = true;
    		update_option('ngg_options', $ngg->options);
    	}

        do_action( 'ngg_update_addgallery_page' );

    }

    /**
     * Render the page content
     *
     * @return void
     */
    function controller() {
        global $ngg, $nggdb;

    	// check for the max image size
    	$this->maxsize    = nggGallery::check_memory_limit();

    	//get all galleries (after we added new ones)
    	$this->gallerylist = $nggdb->find_all_galleries('gid', 'DESC');

        $this->defaultpath = $ngg->options['gallerypath'];

        // link for the flash file
		$swf_upload_link = admin_url('/?nggupload');

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

	<?php //include('templates/social_media_buttons.php'); ?>

	<div class='wrap'><h2 class='title'><?php echo_h(_("Add Gallery / Images"))?></h2>

	<?php if($ngg->options['swfUpload'] && !empty ($this->gallerylist) ) { ?>

    <!-- plupload script -->
    <script type="text/javascript">
    //<![CDATA[
    var resize_height = <?php echo (int) $ngg->options['imgHeight']; ?>,
    	resize_width = <?php echo (int) $ngg->options['imgWidth']; ?>;

    jQuery(document).ready(function($) {
		if ($(this).data('ready')) return;

		// Listen for events emitted in other frames
		if (window.Frame_Event_Publisher) {

			// If a new gallery has been created, add it to the drop-downs of
			// available galleries
			Frame_Event_Publisher.listen_for('attach_to_post:new_gallery', function(data){
				var gallery_id = data.gallery[data.gallery.id_field];
				var gallery_title = data.gallery.title.replace(/\\&/, '&');
				var option = $('<option/>').attr({
					value:	gallery_id
				});
				option.html(gallery_id+' - '+gallery_title);
				$('#galleryselect').append(option);
				$('select[name="zipgalselect"]').append(option.clone());
			});

			// If a gallery has been deleted, remove it from the drop-downs of
			// available galleries
			Frame_Event_Publisher.listen_for('attach_to_post:gallery_deleted', function(data){
				var gallery_id = data.gallery_id;
				$('#galleryselect option[value="'+gallery_id+'"]').remove();
				$('select[name="zipgalselect"] option[value="'+gallery_id+'"]').remove();
			});

			// If a gallery has been modified, ensure that we're displaying
			// the correct gallery title
			Frame_Event_Publisher.listen_for('attach_to_post:gallery_modified', function(data){
				var gallery_id		= data.gallery[data.gallery.id_field];
				var gallery_title	= data.gallery.title;
				$('#galleryselect option[value="'+gallery_id+'"]').html(gallery_title.replace(/\\&/g, "&"));
				$('select[name="zipgalselect"] option[value="'+gallery_id+'"]').html(gallery_title.replace(/\\&/g, "&"));
			});
		}

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
			var accordion_content = $('#uploadimage').next('div');
			accordion_content[0].scrollTop = accordion_content.height();

				// when loaded into an iframe ensure we update iframe height accordingly
				if (top != window) {
					if (typeof(parent.resize_attach_to_post_tab) != 'undefined') {
						parent.resize_attach_to_post_tab(window.frameElement, true);
					}
					else {
						jQuery(parent.document).find('iframe.ngg-attach-to-post').each(function (i, elem) {
							var jElem = jQuery(elem);
							jElem.height(jElem.contents().height());
						});
					}
				}
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
            $('html, body').animate({
                scrollTop: $('#' + file.id).position().top - 20
            });
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

		$(this).data('ready', true);
    });
    //]]>
    </script>
	<?php } else { ?>
	<!-- MultiFile script -->
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery(document).ready(function(){
			jQuery('#imagefiles').MultiFile({
				STRING: {
			    	remove:'[<?php _e('remove', 'nggallery') ;?>]'
  				}
		 	});
		});
	/* ]]> */
	</script>
	<?php } ?>
	<!-- jQuery Tabs script -->
	<script type="text/javascript">
	/* <![CDATA[ */
		jQuery(document).ready(function(){
            jQuery('html,body').scrollTop(0);
			jQuery('#accordion').accordion({ clearStyle: true, autoHeight: false });
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
				if (top != window) {
					if (typeof(parent.resize_attach_to_post_tab) != 'undefined') {
						parent.resize_attach_to_post_tab(window.frameElement, true);
					}
				}
		    });
		});
	/* ]]> */
	</script>
		<div id="accordion">
			<?php foreach ($tabs as $tab_key => $tab_name): ?>
			<h3 id="<?php echo esc_attr($tab_key) ?>">
				<a href='#'><?php echo_h($tab_name)?></a>
			</h3>
			<div>
				<?php
					$method = 'tab_'.$tab_key;
					if (method_exists($this, $method)) {
						call_user_func(array(&$this, $method));
					}
					else do_action('ngg_tab_content_'.$tab_key);
				?>
			</div>
			<?php endforeach; ?>
		</div>
</div> <!-- end of wrap-->
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

        if ( wpmu_enable_function('wpmuZipUpload') && nggGallery::current_user_can( 'NextGEN Upload a zip' ) )
            $tabs['zipupload'] = __('Upload a Zip-File', 'nggallery');

        if ( wpmu_enable_function('wpmuImportFolder') && nggGallery::current_user_can( 'NextGEN Import image folder' ) )
            $tabs['importfolder'] = __('Import image folder', 'nggallery');

    	$tabs = apply_filters('ngg_addgallery_tabs', $tabs);

    	return $tabs;

    }

    function tab_addgallery() {
    ?>
		<!-- create gallery -->
		<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $this->filepath; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('New Gallery', 'nggallery') ;?>:</th>
				<td><input type="text" size="35" name="galleryname" value="" /><br />
				<?php if(!is_multisite()) { ?>
				<?php _e('Create a new , empty gallery below the folder', 'nggallery') ;?>  <strong><?php echo $this->defaultpath ?></strong><br />
				<?php } ?>
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
					foreach($this->gallerylist as $gallery) {
						if ( !nggAdmin::can_manage_this_gallery($gallery->author) )
							continue;
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . esc_attr( $name ). '</option>' . "\n";
					}
				?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<br /><?php echo _e('Note : The upload limit on your server is ','nggallery') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?>
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
        global $ngg;
        // check the cookie for the current setting
        $checked = get_user_setting('ngg_upload_resize') ? ' checked="true"' : '';
    ?>
    	<!-- upload images -->
		<form name="uploadimage" id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->filepath.'#uploadimage'; ?>" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Upload image', 'nggallery') ;?></th>
                <?php if ($ngg->options['swfUpload']) { ?>
				<td>
                <div id="plupload-upload-ui">
                	<div>
                    	<?php _e( 'Choose files to upload' ); ?>
                    	<input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" />
                	</div>
                	<p class="ngg-dragdrop-info howto" style="display:none;" ><?php _e('Or you can drop the files into this window.'); ?></p>
                    <div id='uploadQueue'></div>
                    <p><label><input name="image_resize" type="checkbox" id="image_resize" value="true"<?php echo $checked; ?> />
                        <?php printf( __( 'Scale images to max width %1$dpx or max height %2$dpx', 'nggallery' ), (int) $ngg->options['imgWidth' ], (int) $ngg->options[ 'imgHeight' ] ); ?>
                        <div id='image_resize_pointer'>&nbsp;</div>
                        </label>
                    </p>

                 </div>
                </td>
                <?php } else { ?>
				<td><span id='spanButtonPlaceholder'></span><input type="file" name="imagefiles[]" id="imagefiles" size="35" class="imagefiles"/></td>
                <?php } ?>
            </tr>
			<tr valign="top">
				<th scope="row"><?php _e('in to', 'nggallery') ;?></th>
				<td><select name="galleryselect" id="galleryselect">
				<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
				<?php
					foreach($this->gallerylist as $gallery) {

						//special case : we check if a user has this cap, then we override the second cap check
						if ( !current_user_can( 'NextGEN Upload in all galleries' ) )
							if ( !nggAdmin::can_manage_this_gallery($gallery->author) )
								continue;

						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . esc_attr( $name ) . '</option>' . "\n";
					}					?>
				</select>
				<br /><?php echo $this->maxsize; ?>
				<br /><?php if ((is_multisite()) && wpmu_enable_function('wpmuQuotaCheck')) display_space_usage(); ?></td>
			</tr>
			</table>
			<div class="submit">
				<?php if ($ngg->options['swfUpload']) { ?>
				<input type="submit" name="disable_flash" id="disable_flash" title="<?php _e('The batch upload requires Adobe Flash 10, disable it if you have problems','nggallery') ?>" value="<?php _e('Disable flash upload', 'nggallery') ;?>" />
				<?php } else { ?>
				<input type="submit" name="enable_flash" id="enable_flash" title="<?php _e('Upload multiple files at once by ctrl/shift-selecting in dialog','nggallery') ?>" value="<?php _e('Enable flash based upload', 'nggallery') ;?>" />
				<?php } ?>
				<input class="button-primary" type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e('Upload images', 'nggallery') ;?>" />
			</div>
		</form>
    <?php
    }
}
?>
