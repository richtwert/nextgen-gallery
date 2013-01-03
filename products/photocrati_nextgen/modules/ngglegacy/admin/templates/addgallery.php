<?php //include('templates/social_media_buttons.php'); ?>
<div class='wrap'>
	<h2 class='title'><?php echo_h(_("Add Gallery / Images"))?></h2>

	<?php if ($gallerylist): ?>
		<script type="text/javascript">
			var resize_height	= <?php echo $image_height ?>;
			var resize_width	= <?php echo $image_width ?>;

			jQuery(document).ready(function($) {
				if ($(this).data('ready')) return;

				// Listen for events emitted in other frames
				if (window.Frame_Event_Publisher) {

					// If a new gallery has been created, add it to the drop-downs of
					// available galleries
					Frame_Event_Publisher.listen_for('attach_to_post:new_gallery', function(){
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
					Frame_Event_Publisher.listen_for('attach_to_post:manage_galleries', function(){
						window.location.reload(true);
					});
				}

				try {
				window.uploader = new plupload.Uploader({
					runtimes: '<?php echo $plupload_runtimes ?>',
					browse_button: 'plupload-browse-button',
					container: 'plupload-upload-ui',
					drop_element: 'uploadimage',
					file_data_name: 'Filedata',
					max_file_size: '<?php echo $max_filesize ?>',
					url: '<?php echo esc_js( $swf_upload_link ); ?>',
					flash_swf_url: '<?php echo esc_js( $flash_swf_url ); ?>',
					silverlight_xap_url: '<?php echo esc_js( $silverlight_xap_url ); ?>',
					filters: [
						{title: '<?php echo esc_js( __('Image Files', 'nggallery') ); ?>', extensions: '<?php echo esc_js( str_replace( array('*.', ';'), array('', ','), $file_types)  ); ?>'}
					],
					multipart: true,
					urlstream_upload: true,
					multipart_params : <?php echo $post_params ?>,
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
				}
				catch (ex) {
					console.log(ex);
				}

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

				// JQuery Tabs
				jQuery('html,body').scrollTop(0);
				jQuery('#accordion').accordion({ clearStyle: true, autoHeight: false });

				// Browse filesystem
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

			$(this).data('ready', true);
		});
		</script>
	<?php endif; ?>

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
</div>
