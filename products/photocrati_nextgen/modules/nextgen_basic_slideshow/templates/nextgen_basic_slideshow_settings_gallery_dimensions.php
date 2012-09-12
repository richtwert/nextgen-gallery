<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_gallery_width'>
            <?php echo_h($gallery_dimensions_label); ?>
        </label>
    </td>
    <td>
        w <input type='number'
               id='<?php echo esc_attr($display_type_name); ?>_gallery_width'
               name='<?php echo esc_attr($display_type_name); ?>[gallery_width]'
               class='ngg_slideshow_gallery_width'
               placeholder='<?php _e('Width'); ?>'
               min='1'
               required='required'
               value='<?php echo esc_attr($gallery_width); ?>'/> /
        <input type='number'
               id='<?php echo esc_attr($display_type_name); ?>_gallery_height'
               name='<?php echo esc_attr($display_type_name); ?>[gallery_height]'
               class='ngg_slideshow_gallery_height'
               placeholder='<?php _e('Height'); ?>'
               min='1'
               required='required'
               value='<?php echo esc_attr($gallery_height); ?>'/>
        h
    </td>
</tr>
