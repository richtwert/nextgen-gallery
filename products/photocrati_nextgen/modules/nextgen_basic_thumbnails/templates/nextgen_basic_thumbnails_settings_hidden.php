<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'>
            <?php echo_h($show_all_in_lightbox_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'
               name='<?php echo esc_attr($display_type_name); ?>[show_all_in_lightbox]'
               class='ngg_thumbnail_show_all_in_lightbox'
               value='1'
               <?php echo checked(1, $show_all_in_lightbox); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox_no'
               name='<?php echo esc_attr($display_type_name); ?>[show_all_in_lightbox]'
               class='ngg_thumbnail_show_all_in_lightbox'
               value=''
               <?php echo checked('', $show_all_in_lightbox); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox_no'><?php _e('No'); ?></label>

        <br/><?php echo_h($show_all_in_lightbox_desc); ?>
    </td>
</tr>
