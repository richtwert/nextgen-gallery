<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_gallery_width'>
            <?php echo_h($gallery_dimensions_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_gallery_width'
               name='<?php echo esc_attr($display_type_name); ?>[gallery_width]'
               class='ngg_slideshow_gallery_width'
               value='<?php echo esc_attr($gallery_width); ?>'/>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_gallery_height'
               name='<?php echo esc_attr($display_type_name); ?>[gallery_height]'
               class='ngg_slideshow_gallery_height'
               value='<?php echo esc_attr($gallery_height); ?>'/>
    </td>
</tr>
