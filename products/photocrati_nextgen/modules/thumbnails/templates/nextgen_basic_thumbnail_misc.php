<tr>
    <td>
        <label for="<?php echo esc_attr($display_type_name) ?>_template">
            <?php echo_h($thumbnail_template_label) ?>
        </label>
    </td>
    <td>
        <select id="<?php echo esc_attr($display_type_name)?>_template"
                class="ngg_thumbnail_template"
                name="<?php echo esc_attr($display_type_name) ?>[thumbnail_template">
            <option><?php echo_h('Default'); ?></option>
            <option><?php echo_h('Option 2'); ?></option>
            <option><?php echo_h('Option 3'); ?></option>
        </select>
    </td>
</tr>
