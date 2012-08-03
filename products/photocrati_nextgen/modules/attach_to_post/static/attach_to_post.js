jQuery(function($){

    $.fn.dom_val = function(value){
        if (!value) return this.val();
        else {
            this.each(function(){
               var $this = $(this);
               switch(this.tagName) {
                   case 'INPUT':
                       switch($this.attr('type')) {
                           case 'checkbox':
                           case 'cbox':
                           case 'radio':
                               if (value != 0 && value != false && value != '')
                                   $this.attr('checked', 'checked');
                               else
                                   $this.removeAttr('checked');
                               break;
                           default:
                               $this.attr('value', value);
                               break;
                       }
                       break;
                   case 'TEXTAREA':
                       $this.text(value);
                       break;
                   case 'SELECT':
                       if (!$this.hasAttr('multiple')) {
                         $this.find('option').each(function(){
                            $(this).removeAttr('selected');
                         });
                       }
                       $this.find('option[value="'+value+'"]').attr('selected', 'selected');
                       break;
               }
            });
        }
    }


    var NEXTGEN_ATTACH_TO_POST_DATA = 'NextGen_AttachToPost';

    $.fn.NextGen_AttachToPost = function(){

		// Activate accordions
		$('.accordion').accordion({
			active: false,
			autoHeight: false
		});

        // Ensure that our dependencies are met
        if (!$.fn.pluploadQueue) $.error('NextGen Attach To Post requires Plupload');

        // Get the gallery instance id that we're editing
        var attached_gallery_id = window.location.search.match(/attached_gallery_id=([^&]+)/);
        if (attached_gallery_id) attached_gallery_id = attached_gallery_id[1];

        // Get the post id that we're attaching to
        var post_id = window.location.search.match(/post_id=(\d+)/);
        if (post_id) post_id = post_id[1];

		// We'll be attaching data to the DOM
        var $el = $this = $(document);

        // Attach the internal data
        var obj = {
            ID:					 attached_gallery_id,
            post_id:             post_id,
            ajax_url:            nextgen_attach_settings.ajax_url,
            max_file_size:       nextgen_attach_settings.max_file_size,
            plupload_swf_url:    nextgen_attach_settings.plupload_swf_url,
            plupload_xap_url:    nextgen_attach_settings.plupload_xap_url,
            gallery_id:          $('#gallery_id').val(),
            gallery_name:        $('#gallery_name').val(),
            gallery_description: $('#gallery_description').val()
        };
        $this.data(NEXTGEN_ATTACH_TO_POST_DATA, obj);


        // Close the window when the escape button is pressed
        $el.keydown(function(e){
            if (e.keyCode == 27) parent.close_tinymce_windows();
            return;
        });

        /*** Gallery source tab **********************************************/

        // Activate plupload
        var uploader = $('#uploader');
        uploader.pluploadQueue({
            runtimes: 'html5,gears,flash,silverlight,browserplus',
            url:                    obj.ajax_url,
            max_file_size:          obj.max_file_size,
            unique_names:           true,
            drop_element:           'uploader_filelist',
            filters:                [
                    {title: "Images", extensions: "jpg,jpeg,png,gif,tiff"},
                    {title: "Archives", extensions: "zip"}
            ],
            flash_swf_url:          obj.plupload_swf_url,
            silverlight_xap_url:    obj.plupload_xap_url
        });

        // Add the plupload buttons above the file list
        footer = $('.plupload_filelist_footer').detach();
        $('.plupload_content').prepend(footer);

        // Get plupload instance and start attaching events
        uploader = uploader.pluploadQueue();

		// When a file has been successfully uploaded, call file_uploaded()
        uploader.bind('FileUploaded', $.fn.NextGen_AttachToPost.file_uploaded);

		// When there was an upload error, display it to the user
        uploader.bind('Error', function(up, err){
            $('#gallery_source_tab .errors').html(err).show();
        });

		// When the upload is complete, populate the images
		// in the image tab
        uploader.bind('UploadComplete', function(){

            // Populate the images from the new gallery
            $.fn.NextGen_AttachToPost.populate_images('gallery');

            // Restore the "Attach Gallery" button
            $('#save_button').removeAttr('disabled').val('Attach Gallery');

            // Move to the gallery type tab automatically
			// TODO: Broken
            $('a[href="#Gallery_Type"').parents('h3').click();

        });

		// Before an image is uploaded, add the gallery information
		// to the request. This ensures that a gallery gets created if
		// needed
        uploader.bind('BeforeUpload', function(up, info){

            // While uploading images, we need to temporary disable the
            // "Attach Gallery" button. Restored in the UploadComplete() callback
            $('#save_button').attr('disabled', 'disabled').val('Uploading images...');

            // Ensure that the gallery name and description is
            // part of the request
            var obj = $el.data(NEXTGEN_ATTACH_TO_POST_DATA);
            obj.action = '_upload_image';
            obj.gallery_name = $('#gallery_name').val();
            obj.gallery_description = $('#gallery_description').val();
            delete obj.max_file_size;
            up.settings.multipart_params = obj;
        });


        /*** Gallery source tab *********************************************/

        // Add callback when the gallery source changes
        $('#gallery_source').change(
            $.fn.NextGen_AttachToPost.changed_source
        );

        // Add callback when an existing gallery is selected
        $('#gallery_id').change(
            $.fn.NextGen_AttachToPost.changed_gallery
        );

        // Hide all fields for gallery sources
        $this.find('.gallery_source_fields').hide();


        /*** Gallery Type tab **********************************************/

        $('.gallery_type_selector').change(
            $.fn.NextGen_AttachToPost.gallery_type_changed
        );


        /*** Submit/save button ********************************************/
        $('#save_button').click(
            $.fn.NextGen_AttachToPost.save_button_clicked
        );


        /** Image options tab **********************************************/

        // Hide/show (or include or not include) a selected image in the
        // attached gallery
        $('#image_options_tab .hide_or_show').live('click', function(e){
            e.preventDefault();
            var $this = $(this);
            var image_id = $this.attr('rel');
            var hidden_field = $('.image_included[rel="'+image_id+'"]');
            var image = $('.image[rel="'+image_id+'"]');

            // Hide the image
            if (hidden_field.val() > 0) {
                hidden_field.val(0);
                image.addClass('hidden_img');
                $(this).text('Show');
            }

            // Show the image
            else {
                hidden_field.val(1);
                image.removeClass('hidden_img');
                $(this).text('Hide');
            }
        });

        // Display image edit form
        $('#image_options_tab .edit').live('click', function(e){
           e.preventDefault();
           var $this = $(this);
           var image_id = $this.attr('rel');

           $.fn.NextGen_AttachToPost.display_edit_image_form(image_id);
        });

        // Hide (or un-include) all images
        $('#image_options_tab #display_none').live('click', function(e){
           e.preventDefault();
           $('#image_options_tab .hide_or_show').each(function(){
              var $this = $(this);
              var image_id = $this.attr('rel');
              var hidden_field = $('.image_included[rel="'+image_id+'"]');
              if (hidden_field.val() > 0) $this.click();
           });
        });

        // Show (or include) all images
        $('#image_options_tab #display_all').live('click', function(e){
           e.preventDefault();
           $('#image_options_tab .hide_or_show').each(function(){
              var $this = $(this);
              var image_id = $this.attr('rel');
              var hidden_field = $('.image_included[rel="'+image_id+'"]');
              if (hidden_field.val() <= 0) $this.click();
           });
        });


        /*** Set defaults for all tabs *************************************/

        // Note the HTML already selects the correct source value, just trigger a change for new/existing sources
        $('#gallery_source').change()
    };


    // Displays the edit image form for the particular image
    $.fn.NextGen_AttachToPost.display_edit_image_form = function(image_id)
    {
        var hidden_form = $('.image_form[rel="'+image_id+'"]').clone().removeClass('hidden');
        var edit_image_form = $('#edit_image_form');
        var edit_image_form_errors = edit_image_form.find('.errors');

        // Create a function that will be used to display
        // a hidden image form
        var display_edit_image_form = function(hidden_form) {
             edit_image_form.empty()
                 .append(hidden_form)
                 .append("<input type='button' id='done_editing' value='Done'/>")
                 .fadeIn('slow', function(){
                     edit_image_form.removeClass('hidden');
                     $('body').scrollTop(edit_image_form.offset().top);
                 });
        };

        // Restore the image's hidden form when the 'done editing' button
        // is clicked
        $('#done_editing').live('click', function(){
            var $this = $(this);
            var new_form = edit_image_form.find('table');
            var image_id = new_form.attr('rel');
            var gallery_image_id = $('#.image .gallery_image_id').val();

            // First, we need to validate the image
            $this.val('Validating...').attr('disabled', 'disabled');
            var data = $.fn.NextGen_AttachToPost.get_obj();
            data.action = '_validate_image';
            data.image_id = gallery_image_id;
            new_form.find('*[name*="image"]').each(function(){
                var $this = $(this);
                var matches = $this.attr('name').match(/images\[\d+\]\[(.*)\]/);
                var field_name = matches[1];
                data[field_name] = $this.val();
            });
            $.post(data.ajax_url, data, function(data, status, xhr){
                if (typeof(data) != 'object') data = JSON.parse(data);
                if (data.success) {
                    edit_image_form_errors.fadeOut().empty();
                    edit_image_form.fadeOut();
                    var parent_image_form = $('#image_list .image_form[rel="'+image_id+'"]');
                    var parent_image_form = $('#image_list .image_form[rel="'+image_id+'"]');
                    new_form.find('input, textarea, select').each(function(){
                        var $this = $(this);
                        parent_image_form.find('#'+$this.attr('id')).dom_val($this.val());
                    });
                    new_form.detach();
                }
                else if (data.error) alert(data.error);
                else {
                    edit_image_form_errors.append('<ul></ul>');
                    for (err in data.validation_errors) {
                        edit_image_form_errors.find('ul').append(
                            "<li>"+data.validation_errors[err]+"</li>"
                        );
                    }
                    edit_image_form_errors.fadeIn();
                }
                $this.removeAttr('disabled').val('Save');
            });
        });

        // If the edit form is already being displayed, roll it up,
        // add the new form, and then roll back down
        if (edit_image_form.find('table').length > 0) {
            edit_image_form.fadeOut('slow', function(){
               display_edit_image_form(hidden_form);
            });
        }

        // The edit image form isn't being display - roll it down
        else {
            display_edit_image_form(hidden_form);
        }
    };


    // Callback for when the gallery source changes
    $.fn.NextGen_AttachToPost.changed_source = function(e, no_images)
    {
        var $this = $(this);
        var obj = $.fn.NextGen_AttachToPost.get_obj();

        // Are we to show the plupload image uploader?
        var upload_row = $('#upload_row');
        if ($this.val().match(/new_gallery|existing_gallery/)) {

            // Show!
            if (upload_row.hasClass('hidden')) {
                upload_row.fadeIn('slow', function(){
                    $(this).removeClass('hidden');
                });
            }
        }
        else {

            // Hide!
            upload_row.fadeOut('slow', function(){
               $(this).addClass('hidden');
            });
        }


        // Hide all gallery source fields except the appropriate one
        var visible_fields = $('.gallery_source_fields:visible');
        var selected_fields = $('#'+$this.val()+'_fields');
        if (visible_fields.length) {
            visible_fields.slideUp('slow', function(){
                selected_fields.slideDown();
            });
        }
        else selected_fields.slideDown();

        // Update object
        obj.gallery_source = $this.val();
        $.fn.NextGen_AttachToPost.update_obj(obj);

        // Trigger a gallery change
        if (obj.gallery_source == 'existing_gallery') {
            $('#gallery_id').trigger('change', [no_images]);
        }

        // XXX Image sources that don't upload anything need this
        if (!$this.val().match(/new_gallery|existing_gallery/)) {
        	$.fn.NextGen_AttachToPost.populate_images('gallery');
        }
    };

    /**
     * Callback for when an existing gallery selection has changed
    **/
    $.fn.NextGen_AttachToPost.changed_gallery = function(e, no_images)
    {
       var $this = $(this);
       var gallery_id = $this.val();
       var gallery_name = $this.find("option[value='"+gallery_id+"'").text();

       // Update the object
       var obj = $.fn.NextGen_AttachToPost.get_obj();
       obj.gallery_id = gallery_id;
       obj.gallery_name = gallery_name;
       $.fn.NextGen_AttachToPost.update_obj(obj);

       // Populate images
       if (!no_images) $.fn.NextGen_AttachToPost.populate_images('gallery');
    };

    /**
     * Populates the images for the current attached gallery
     * or selected gallery
     */
    $.fn.NextGen_AttachToPost.populate_images = function()
    {
       // While images are loading, "Attach Gallery" button should temporarily
       // be disabled
       $('#save_button').attr('disabled','disabled').val('Loading images...');

	   var obj = $.fn.NextGen_AttachToPost.get_obj();
	   obj.action = '_get_image_forms';
	   obj.source = $('#gallery_source').val();

       // Get the images from the server
       $.post(obj.ajax_url, obj, function(data, status, xhr){
           if (typeof(data) ==  'string') data = JSON.parse(data);
           $('#image_options_tab').replaceWith(data.html);

		   // Restore save button
           $('#save_button').removeAttr('disabled').val('Attach Gallery');

           // Enable sorting!
		   var $image_list = $('#image_options_tab #image_list');
           var sorted_list = $image_list.sortable({
              handle: 'img',
              scroll: true,
              containment: 'parent',
              axis: 'x'
           });

           // When sorting has been complete, re-order the images
           sorted_list.disableSelection();
           sorted_list.bind('sortstop', function(){
               var count=1;
               $('.image_order').each(function(){
                  $(this).val(count);
                  count++;
               });
           });
       });
    }

    /**
     * Callback for when a file has been succesfully uploaded
     */
    $.fn.NextGen_AttachToPost.file_uploaded = function(uploader, file, info)
    {
        // Parse response
        var response = typeof(info.response) == 'object' ?
            info.response : $.parseJSON(info.response);

        // Did a server error occur?
        if (response.error) {
            $('#gallery_source_tab .errors').html(response.error).show();
        }

        // Were there validation problems?
        else if (response.validation_errors) {
            $('#gallery_source_tab .errors').html(response.error).show();
        }

        // We're good to go
        else {

            // If this gallery didn't exist before, we need to add it to the
            // drop down list
            if ($('#gallery_id option[value="'+response.gallery_id+'"').length == 0) {
                var option = "<option value='";
                option += response.gallery_id+"'>";
                option += $.fn.NextGen_AttachToPost.htmlentities(response.gallery_name);
                option += "</option>";
                $('#gallery_id').append(option);
                $('#gallery_id').val(response.gallery_id);
            }

            // Update the object with the selected gallery id
            var obj = $.fn.NextGen_AttachToPost.get_obj();
            obj.gallery_id = response.gallery_id;
            obj.gallery_source = 'existing_gallery';
            $.fn.NextGen_AttachToPost.update_obj(obj);
        }

        console.log('uploaded');
    };

    // Returns the object associated with the event
    $.fn.NextGen_AttachToPost.get_obj = function()
    {
        return $(document).data('NextGen_AttachToPost');
    };

    $.fn.NextGen_AttachToPost.update_obj = function(obj)
    {
        return $(document).data('NextGen_AttachToPost', obj);
    };

    /**
     * Callback for when a gallery type is selected
     */
    $.fn.NextGen_AttachToPost.gallery_type_changed = function(e)
    {
        var $this = $(this);

        // Temporarily disable the "Attach Gallery" button
        $('#save_button').attr('disabled', 'disabled').val('Loading gallery settings...');

        // Update object
        var obj = $.fn.NextGen_AttachToPost.get_obj();
        obj.gallery_type = $this.val();
        $.fn.NextGen_AttachToPost.update_obj(obj);

        // Populate the gallery display settings tab
        obj.action = '_get_gallery_display_settings_form';
        $.post(obj.ajax_url, obj, function(data, status, xhr){
            $('#gallery_display_tab').html(data.html);

            // Restore the "Attach Gallery" button
            $('#save_button').removeAttr('disabled').val('Attach Gallery');
        });
    };


    /**
     * Callback for when the save button is clicked
     */
    $.fn.NextGen_AttachToPost.save_button_clicked = function(e)
    {
        e.preventDefault();

        // Disable submit button
        $('#save_button').val('Saving...').attr('disabled', 'disabled');

        // Hide any previous errors
        $('#errors').fadeOut();

        // Remove any of the previous errors
        $('#errors').empty();
        var obj = $.fn.NextGen_AttachToPost.get_obj();

        // Ensure that edit_image_form is empty
        $('#edit_image_form').empty();

        // Serialize the form
        var data = $('#attach_gallery').serializeObject();

        // Submit AJAX request
        var data = $.extend(data, obj);
        data.ID = obj.ID;
        data.action = "_save_attached_gallery";
        $.post(obj.ajax_url, data, function(data, status, xhr){

            var errors = [];

            // Collect validation errors for gallery settings
            if (data.gallery_setting_validation_errors) {
                for (var prop in data.gallery_setting_validation_errors) {
                    for (var err in data.gallery_setting_validation_errors[prop]) {
                        errors.push(
                            data.gallery_setting_validation_errors[prop][err]
                        );
                    }
                }
            }

            // Collect validation errors for the attached gallery itself
            if (data.validation_errors) {
                for (var prop in data.validation_errors) {
                    errors.push(
                        data.validation_errors[prop]
                    );
                }
            }

            // Collect validation errors for images
			// TODO: Need to display image validation errors
            if (data.image_validation_errors) {

            }

            // Did the attached gallery save correctly?
            if (data.saved) {

                // Update the obj
                var obj = $.fn.NextGen_AttachToPost.get_obj();
                obj.ID = data.ID;
                $.fn.NextGen_AttachToPost.update_obj(obj);
                $('attached_gallery_id').val(obj.ID);

                // Call tinyMCE helper methods to add the
                // attached gallery to the editor. We can then close
                // the 'attach to post' interface
                parent.append_attached_gallery(
                    obj.ID,
                    obj.gallery_name
                );
                parent.close_tinymce_windows();
            }

            // Display the errors
            else {
                window.scrollTo(0, 0);
                window.parent.scrollTo(0,0);
                $('#errors').append("<ul></ul>");
                for (var err in errors) {
                    $('#errors ul').append("<li>"+errors[err]+"</li>");
                }
                $('#errors').fadeIn();
            }

            // Restore save button
            $('#save_button').val('Attach Gallery').removeAttr('disabled');

        }); // end of post
    };

    // Encodes all html entities
    $.fn.NextGen_AttachToPost.htmlentities = function(value){
        return $('<div/>').text(value).html().replace(/'/g, "&apos;").replace(/"/g, "&quot;");
    };

    jQuery.fn.serializeObject = function() {
      var arrayData, objectData;
      arrayData = this.serializeArray();
      objectData = {};

      $.each(arrayData, function() {
        var value;

        if (this.value != null) {
          value = this.value;
        } else {
          value = '';
        }

        if (objectData[this.name] != null) {
          if (!objectData[this.name].push) {
            objectData[this.name] = [objectData[this.name]];
          }

          objectData[this.name].push(value);
        } else {
          objectData[this.name] = value;
        }
      });

      return objectData;
    };


});

jQuery(function($){
   $(this).NextGen_AttachToPost();
});
