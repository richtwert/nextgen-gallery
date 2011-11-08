$j = jQuery.noConflict();
$j(function(){
    $j('body').bind('loading_gallery_instance', function(e, gallery_instance){
    
        // Populate display template
        $j('#display_template').val(gallery_instance.display_template);
        
        // Populate thumbnail dimensions
        $j("input[name='settings[thumbnail_width]']").val(gallery_instance.settings.thumbnail_width);
        $j("input[name='settings[thumbnail_height]']").val(gallery_instance.settings.thumbnail_height);
        
        // Select appropriate croping option
        $j("input[name='settings[thumbnail_crop]']").each(function(){
            if ($j(this).val() == gallery_instance.settings.thumbnail_crop) {
                $j(this).attr('checked', 'checked');
            }
            else {
                $j(this).removeAttr('checked');
            }
        });
        
        // Populate thumbnail quality
        $j("input[name='settings[thumbnail_quality]']").val(gallery_instance.settings.thumbnail_quality);
    });
});