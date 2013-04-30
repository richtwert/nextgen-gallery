<div class='ngg-tagcloud' id="gallery_<?php echo $displayed_gallery_id ?>">
    <?if ($tagcloud): ?>
    <?php print $tagcloud; ?>
    <?php else: ?>
        No images have been tagged.
    <?php endif ?>
</div>

<script type="text/javascript">
    (function($){
        $('#gallery_<?php echo $displayed_gallery_id ?>').css('opacity', 0.0);
        $(document).on('lazy_resources_loaded', function(){
            $('#gallery_<?php echo $displayed_gallery_id ?>').css('opacity', 1.0);
        });
    })(jQuery);
</script>