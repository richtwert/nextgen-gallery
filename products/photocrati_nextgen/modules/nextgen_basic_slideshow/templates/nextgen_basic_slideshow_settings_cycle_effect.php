<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_cycle_effect'>
            <?php echo_h($cycle_effect_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_cycle_effect'
               name='<?php echo esc_attr($display_type_name); ?>[cycle_effect]'
               class='ngg_slideshow_cycle_effect'
               value='<?php echo esc_attr($cycle_effect); ?>' />
    </td>
</tr>
