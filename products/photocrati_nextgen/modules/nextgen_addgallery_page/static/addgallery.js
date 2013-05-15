jQuery(function($){
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
            $('#gallery_id').append(option);
        });

        // If a gallery has been deleted, remove it from the drop-downs of
        // available galleries
        Frame_Event_Publisher.listen_for('attach_to_post:manage_galleries', function(){
            window.location.reload(true);
        });
    }
});