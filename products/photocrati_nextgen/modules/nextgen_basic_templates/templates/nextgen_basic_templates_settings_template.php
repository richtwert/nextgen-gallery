<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_template' class='tooltip'>
            <?php echo_h($template_label); ?>
            <span><?php echo_h($template_text); ?></span>
        </label>
    </td>
    <td>
        <div class='ngg_settings_template_wrapper'>
            <select name='<?php echo esc_attr($display_type_name); ?>[template]'
                    id='<?php echo esc_attr($display_type_name); ?>_template>'
                    class='ngg_thumbnail_template ngg_settings_template'>
                <option value=''></option>
                <?php foreach ($templates as $file => $label) { ?>
                    <option value="<?php echo $file; ?>" <?php selected($chosen_file, $file, TRUE); ?>><?php echo_h($label); ?></option>
                <?php } ?>
            </select>
        </div>
    </td>
</tr>
