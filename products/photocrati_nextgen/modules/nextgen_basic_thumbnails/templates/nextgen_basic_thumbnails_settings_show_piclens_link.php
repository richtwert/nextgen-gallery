<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_piclens_link'>
            <?php echo_h($show_piclens_link_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_piclens_link'
               name='<?php echo esc_attr($display_type_name); ?>[show_piclens_link]'
               class='ngg_thumbnail_show_piclens_link'
               value='1'
               <?php echo checked(1, intval($show_piclens_link)); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_piclens_link'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_show_piclens_link_no'
               name='<?php echo esc_attr($display_type_name); ?>[show_piclens_link]'
               class='ngg_thumbnail_show_piclens_link'
               value='0'
               <?php echo checked(0, $show_piclens_link); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_piclens_link_no'><?php _e('No'); ?></label>
    </td>
</tr>
