<tr id='tr_<?php print esc_attr("{$display_type_name}_{$name}"); ?>' class='<?php print !empty($hidden) ? 'hidden' : ''; ?>'>
    <td>
        <label for="<?php print esc_attr($display_type_name . '_' . $name); ?>"
               class="<?php if (!empty($text)) { echo 'tooltip'; } ?>">
            <?php print $label; ?>
            <?php if (!empty($text)) { ?>
                <span>
                    <?php print $text; ?>
                </span>
            <?php } ?>
        </label>
    </td>
    <td>
        <select id="<?php print esc_attr($display_type_name . '_' . $name); ?>"
                name="<?php print esc_attr($display_type_name . '[' . $name . ']'); ?>"
                class="<?php print esc_attr($display_type_name . '_' . $name); ?>">
            <?php foreach ($options as $key => $val) { ?>
                <?php if (is_array($options)) { $key = $val; } ?>
                <option value='<?php print $key; ?>' <?php selected($key, $value); ?>><?php print _($val); ?></option>
            <?php } ?>
        </select>
    </td>
</tr>