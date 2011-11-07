$j = jQuery.noConflict();
$j(function(){
    $j('body').bind('loading_gallery_instance', function(e, gallery_instance){
    
        // Populate display template
        $j('#display_template').val(gallery_instance.display_template);
        
        // Populate thumbnail dimensions
        $j("input[name='thumbnails[width]']").val(gallery_instance.thumbnails.width);
        $j("input[name='thumbnails[height]']").val(gallery_instance.thumbnails.height);
        
        // Select appropriate croping option
        $j("input[name='thumbnails[crop]']").each(function(){
            if ($j(this).val() == gallery_instance.thumbnails.crop) {
                $j(this).attr('checked', 'checked');
            }
            else {
                $j(this).removeAttr('checked');
            }
        });
        
        // Populate thumbnail quality
        $j("input[name='thumbnails[quality]']").val(gallery_instance.thumbnails.quality);
    });
});