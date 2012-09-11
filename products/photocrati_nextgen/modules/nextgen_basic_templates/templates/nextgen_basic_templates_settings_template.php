<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_template'>
            <?php echo_h($template_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_template'
               name='<?php echo esc_attr($display_type_name); ?>[template]'
               class='ngg_thumbnail_template ngg_settings_template'
               value='<?php echo esc_attr($template); ?>'>
    </td>
</tr>
