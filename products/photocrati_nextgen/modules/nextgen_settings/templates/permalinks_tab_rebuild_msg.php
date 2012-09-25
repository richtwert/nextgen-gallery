<?php
foreach (array_keys($messages) as $key) {
    $message = sprintf($messages[$key], "<span class='ngg-count-current'>0</span>", "<span class='ngg-count-total'>" . $total[$key] . "</span>");
    echo "<div class='$key updated'><p class='ngg'>{$message}</p></div>";
}
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var ajax_url = '<?php echo $ajax_url; ?>',
                _action  = 'images',
                images   = <?php echo $total['images']; ?>,
                gallery  = <?php echo $total['gallery']; ?>,
                album    = <?php echo $total['album']; ?>,
                total    = 0,
                offset   = 0,
                count    = 50;
        var $display = $('.ngg-count-current');
        $('.finished, .gallery, .album').hide();
        total = images;

        function call_again() {
            if (offset > total) {
                offset = 0;

                // 1st run finished
                if (_action == 'images') {
                    _action = 'gallery';
                    total = gallery;
                    $('.images, .gallery').toggle();
                    $display.html(offset);
                    call_again();
                    return;
                }

                // 2nd run finished
                if (_action == 'gallery') {
                    _action = 'album';
                    total = album;
                    $('.gallery, .album').toggle();
                    $display.html(offset);
                    call_again();
                    return;
                }

                // 3rd run finished, exit now
                if (_action == 'album') {
                    $('.ngg').html('<?php _e( 'Done.', 'nggallery' ); ?>')
                             .parent('div')
                             .hide();
                    $('.finished').show();
                    return;
                }
            }

            $.post(ajax_url, {'_action': _action, 'offset': offset}, function(response) {
                $display.html(offset);
                offset += count;
                call_again();
            });
        }
        call_again();
    });
</script>
