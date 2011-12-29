$j = jQuery.noConflict();
$j(function(){
    $j('body').bind('loading_gallery_instance', function(e, gallery_instance){
    
        // Populate display template
        $j('#display_template').val(gallery_instance.display_template);
        
        // Populate thumbnail dimensions
        $j("input[name='settings[thumbnail_width]']").val(gallery_instance.settings.thumbnail_width);
        $j("input[name='settings[thumbnail_height]']").val(gallery_instance.settings.thumbnail_height);
        
        // Select appropriate cropping option
        $j(".thumbnail_crop").removeAttr('checked');
        if (gallery_instance.settings.thumbnail_crop)
            $j('.thumbnail_crop.yes').prop('checked', true);
        else
            $j('.thumbnail_crop.no').prop('checked', true);
            
        // Select number of images per page
        $j("#ngg_thumbnails_images_per_page").val(gallery_instance.settings.images_per_page);
        
        // Select number of columns
        $j("#ngg_thumbnails_num_of_columns").val(gallery_instance.settings.num_of_columns);
        
        // Show piclens link
        $j(".show_piclens_link").removeAttr('checked');
        if (gallery_instance.settings.show_piclens_link)
            $j('.show_piclens_link.yes').prop('checked', true);
        else
            $j('.show_piclens_link.no').prop('checked', true);
            
        // Show slideshow link
        $j(".show_slideshow_link").removeAttr('checked');
        if (gallery_instance.settings.show_slideshow_link)
            $j('.show_slideshow_link.yes').prop('checked', true);
        else
            $j('.show_slideshow_link.no').prop('checked', true);
            
        // Show thumbnails link
        $j(".show_thumbnails_link").removeAttr('checked');
        if (gallery_instance.settings.show_thumbnails_link)
            $j('.show_thumbnails_link.yes').prop('checked', true);
        else
            $j('.show_thumbnails_link.no').prop('checked', true);
            
        // Link Text
        $j("#ngg_thumbnails_slideshow_link_text").val(gallery_instance.settings.slideshow_link_text);
        $j("#ngg_thumbnails_thumbnail_link_text").val(gallery_instance.settings.thumbnail_link_text);
        $j("#ngg_thumbnails_piclens_link_text").val(gallery_instance.settings.piclens_link_text);
        
        
        // Populate thumbnail quality
        $j("input[name='settings[thumbnail_quality]']").val(gallery_instance.settings.thumbnail_quality);
    });
});