<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'>
            <?php echo_h($ajax_pagination_label); ?>
        </label>
    </td>
    <td>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_ajax_pagination'
               name='<?php echo esc_attr($display_type_name); ?>[ajax_pagination]'
               class='ngg_thumbnail_ajax_pagination'
               value='true'
               <?php echo checked($ajax_pagination); ?>'>
        <?php echo_h($ajax_pagination_desc); ?>
    </td>
</tr>
