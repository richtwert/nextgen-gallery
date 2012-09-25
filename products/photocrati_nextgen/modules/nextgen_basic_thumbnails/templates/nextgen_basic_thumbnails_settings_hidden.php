<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox' class='tooltip'>
            <?php echo_h($show_all_in_lightbox_label); ?>
            <span>
                <?php echo_h($show_all_in_lightbox_desc); ?>
            </span>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'
               name='<?php echo esc_attr($display_type_name); ?>[show_all_in_lightbox]'
               class='ngg_thumbnail_show_all_in_lightbox'
               value='1'
               <?php echo checked(1, intval($show_all_in_lightbox)); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox_no'
               name='<?php echo esc_attr($display_type_name); ?>[show_all_in_lightbox]'
               class='ngg_thumbnail_show_all_in_lightbox'
               value='0'
               <?php echo checked(0, $show_all_in_lightbox); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox_no'><?php _e('No'); ?></label>
    </td>
</tr>
