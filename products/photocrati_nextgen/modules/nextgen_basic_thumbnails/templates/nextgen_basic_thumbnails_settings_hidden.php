<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_hidden'>
            <?php echo_h($hidden_label); ?>
        </label>
    </td>
    <td>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_hidden'
               name='<?php echo esc_attr($display_type_name); ?>[galHiddenImg]'
               class='ngg_thumbnail_hidden'
               value='true'
               <?php echo checked($hidden); ?>'>
        <?php echo_h($hidden_desc); ?>
    </td>
</tr>
