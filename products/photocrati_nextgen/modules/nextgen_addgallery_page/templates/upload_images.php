<div id="gallery_selection">
    <label for="gallery_id">Gallery</label>
    <select id="gallery_id">
        <option value="0">Create a new gallery</option>
        <?php foreach ($galleries as $gallery): ?>
            <option value="<?php echo esc_attr($gallery->{$gallery->id_field}) ?>"><?php echo esc_attr($gallery->title) ?></option>
        <?php endforeach ?>
    </select>
    <input type="text" id="gallery_name" name="gallery_name"/>
</div>

<div id="uploader">
    <p>You browser doesn't have Flash, Silverlight, HTML5, or HTML4 support.</p>
</div>
<script type="text/javascript">
    (function($){
        $(window).on('lazy_resources_loaded', function(){
            window.reinit_plupload = function(up){
                $("#uploader").animate({
                    'opacity': 0.0,
                }, 'slow');
                up.refresh();
                $('#gallery_id').val(0);
                $('#gallery_name').val('');
                init_plupload();
                $("#uploader").animate({
                    'opacity': 1.0
                }, 'slow');
            };

            window.init_plupload = function() {
                var plupload_options =  <?php echo $plupload_options ?>;
                var $gallery_id = $('#gallery_id');
                var $gallery_name = $('#gallery_name').show();
                var $gallery_selection = $('#gallery_selection').detach();

                // Override some final plupload options
                plupload_options.url = photocrati_ajax_url;
                plupload_options.preinit = {
                    PostInit: function(up){
                        // Hide/show the gallery name field
                        $gallery_selection.insertAfter('.plupload_header');
                        var gallery_select    = $('#gallery_id');
                        gallery_select.on('change', function(){
                            var optionSelected = $("option:selected", this);
                            var valueSelected = parseInt(this.value);

                            if (valueSelected == 0) {
                                $('#gallery_name:hidden').fadeIn().focus();
                            }
                            else {
                                $('#gallery_name:visible').fadeOut();
                                gallery_select.focus();
                            }
                        });

                        // Change the text for the dragdrop
                        $('.plupload_droptext').html("Drag image and ZIP files here or click <strong>Add Files</strong>");

                        // Move the buttons
                        var buttons = $('.plupload_buttons').detach();
                        $gallery_selection.append(buttons);

                        // Hide/show the validation for the gallery name field
                        $gallery_name.keypress(function(){
                            if ($gallery_name.val().length > 0) {
                                $gallery_name.removeClass('error');
                            }
                        });

                        // Don't let the uploader continue without a gallery name
                        var start_button = $('#uploader a.plupload_start');
                        start_button.click(function(e){
                            e.preventDefault();

                            var uploader = $('#uploader').pluploadQueue();
                            uploader.settings.multipart_params.gallery_id   = $gallery_id.val();
                            uploader.settings.multipart_params.gallery_name = $gallery_name.val();

                            if ($gallery_id.val() == 0 && $gallery_name.val().length == 0) {
                                $gallery_name.addClass('error');
                                e.stopImmediatePropagation();
                                alert("Please enter a gallery name");
                                $gallery_name.focus();
                                return false;
                            }
                            else {
                                $gallery_name.removeClass('error');
                                return true;
                            }
                        });

                        // Rearrange event handler for start button, to ensure that it has the ability
                        // to execute first
                        var click_events = $._data(start_button[0], 'events').click;
                        click_events.unshift(click_events.pop());

                    },

                    // Refresh the interface after a successful upload
                    StateChanged: function(up){
                        if (up.state == plupload.STOPPED) {
                            setTimeout(function(){
                                reinit_plupload(up);
                            }, 3000);
                        }
                    },

                    BeforeUpload: function(up){
                        up.settings.multipart_params.gallery_id   = $gallery_id.val();
                        up.settings.multipart_params.gallery_name = $gallery_name.val();
                    },

                    // When a gallery has been created, use the same gallery for each request going forward
                    FileUploaded: function(up, file, info){
                        var response = info.response;
                        console.log(response);
                        if (typeof(response) != 'object') {
                            response = JSON.parse(info.response);
                        }
                        up.settings.multipart_params.gallery_id = response.gallery_id;
                    },

                    Error: function(up, args){
                        console.log(args);
                    }
                };

                $("#uploader").pluploadQueue(plupload_options);
            };

            window.init_plupload();
        });
    })(jQuery);
</script>