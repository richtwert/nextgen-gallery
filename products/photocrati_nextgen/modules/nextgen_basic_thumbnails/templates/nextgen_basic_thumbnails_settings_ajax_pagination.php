<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination' class='tooltip'>
            <?php echo_h($ajax_pagination_label); ?>
            <span>
                <?php echo_h($ajax_pagination_desc); ?>
            </span>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'
               name='<?php echo esc_attr($display_type_name); ?>[ajax_pagination]'
               class='ngg_thumbnail_ajax_pagination'
               value='1'
               <?php checked(1, intval($ajax_pagination)); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_ajax_pagination_no'
               name='<?php echo esc_attr($display_type_name); ?>[ajax_pagination]'
               class='ngg_thumbnail_ajax_pagination'
               value='0'
               <?php checked(0, $ajax_pagination); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination_no'><?php _e('No'); ?></label>
    </td>
</tr>
