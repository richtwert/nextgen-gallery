<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_number_of_columns'>
            <?php echo_h($number_of_columns_label); ?>
        </label>
    </td>
    <td>
        <input type='number'
               id='<?php echo esc_attr($display_type_name); ?>_number_of_columns'
               name='<?php echo esc_attr($display_type_name); ?>[number_of_columns]'
               class='ngg_thumbnail_number_of_columns'
               placeholder='<?php _e('# of columns'); ?>'
               min='0'
               required='required'
               value='<?php echo esc_attr($number_of_columns); ?>'>
    </td>
</tr>
