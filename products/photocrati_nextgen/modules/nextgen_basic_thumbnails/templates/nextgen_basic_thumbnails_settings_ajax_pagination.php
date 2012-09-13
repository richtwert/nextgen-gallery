<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'>
            <?php echo_h($ajax_pagination_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'
               name='<?php echo esc_attr($display_type_name); ?>[ajax_pagination]'
               class='ngg_thumbnail_ajax_pagination'
               value='1'
               <?php checked(1, $ajax_pagination); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_ajax_pagination_no'
               name='<?php echo esc_attr($display_type_name); ?>[ajax_pagination]'
               class='ngg_thumbnail_ajax_pagination'
               value=''
               <?php checked('', $ajax_pagination); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination_no'><?php _e('No'); ?></label>
        <br/><?php echo_h($ajax_pagination_desc); ?>
    </td>
</tr>
