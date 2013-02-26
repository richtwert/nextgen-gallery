<div class='ngg-imagebrowser' id='<?php echo $anchor; ?>'>

    <h3><?php echo esc_attr($image->alttext); ?></h3>

    <div class='pic'>
        <a href='<?php echo esc_attr($storage->get_image_url($image)); ?>'
           title='<?php echo esc_attr($image->description); ?>'
           data-image-id='<?php echo esc_attr($image->pid); ?>'
           <?php echo $effect_code ?>>
            <img title='<?php echo esc_attr($image->alttext); ?>'
                 alt='<?php echo esc_attr($image->alttext); ?>'
                 src='<?php echo esc_attr($storage->get_image_url($image)); ?>'/>
        </a>
    </div>

    <div class='ngg-imagebrowser-nav'>

        <div class='back'>
            <a class='ngg-browser-prev'
               id='ngg-prev-<?php echo $previous_pid; ?>'
               href='<?php echo $previous_image_link; ?>'>
                &#9668; <?php _e('Back', 'nggallery'); ?>
            </a>
        </div>

        <div class='next'>
            <a class='ngg-browser-next'
               id='ngg-next-<?php echo $next_pid; ?>'
               href='<?php echo $next_image_link; ?>'>
                <?php _e('Next', 'nggallery'); ?>
                &#9658;
            </a>
        </div>

        <div class='counter'>
            <?php _e('Picture', 'nggallery'); ?> <?php echo $number; ?> <?php _e('of', 'nggallery'); ?> <?php echo $total; ?>
        </div>

        <div class='ngg-imagebrowser-desc'>
            <p>
                <?php echo $image->description; ?>
            </p>
        </div>

    </div>

</div>
