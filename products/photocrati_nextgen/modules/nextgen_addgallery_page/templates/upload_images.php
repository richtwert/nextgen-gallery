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
        // Only run this function once!
        if (typeof($(window).data('ready')) == 'undefined')
            $(window).data('ready', true);
        else return;

        $(window).on('lazy_resources_loaded', function(){
            window.urlencode = function(str){
                str = (str + '').toString();

                // Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
                // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
                return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
                    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
            };

            // Sets the plupload url with necessary parameters in the QS
            window.set_plupload_url = function(gallery_id, gallery_name) {
                var qs = "?gallery_id="+urlencode(gallery_id);
                qs += "&gallery_name="+urlencode(gallery_name);
                return photocrati_ajax_url+qs;
            };

            // Reinitializes plupload
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

            // Initializes plupload
            window.init_plupload = function() {
                var plupload_options =  <?php echo $plupload_options ?>;
                var $gallery_id = $('#gallery_id');
                var $gallery_name = $('#gallery_name').show();
                var $gallery_selection = $('#gallery_selection').detach();
                window.uploaded_image_ids = [];

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
                            uploader.settings.url = window.set_plupload_url($gallery_id.val(), $gallery_name.val());

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
                            $.gritter.add({
                                title: "Upload complete",
                                text: window.uploaded_image_ids.length + " were uploaded successfully."
                            });
                            setTimeout(function(){
                                reinit_plupload(up);
                            }, 3000);
                        }
                    },

                    // When a gallery has been created, use the same gallery for each request going forward
                    FileUploaded: function(up, file, info){
                        var response = info.response;
                        if (typeof(response) != 'object') {
                            response = JSON.parse(info.response);
                        }
                        window.uploaded_image_ids = window.uploaded_image_ids.concat(response.image_ids);
                        up.settings.url = window.set_plupload_url(response.gallery_id, $gallery_name.val());

                        // If we created a new gallery, ensure it's now in the drop-down list, and select it
                        if ($gallery_id.find('option[value="'+response.gallery_id+'"]').length == 0) {
                            var option = $('<option/>').attr('value', response.gallery_id).text(response.gallery_name);
                            $gallery_id.append(option);
                            $gallery_id.val(response.gallery_id);
                            option.attr('selected', 'selected');
                        }
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