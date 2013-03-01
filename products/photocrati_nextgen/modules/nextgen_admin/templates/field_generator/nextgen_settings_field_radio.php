<tr id='tr_<?php print esc_attr("{$display_type_name}_{$name}"); ?>' class='<?php print !empty($hidden) ? 'hidden' : ''; ?>'>
    <td>
        <label for="<?php print esc_attr("{$display_type_name}_{$name}"); ?>"
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
        <input type="radio"
               id="<?php print esc_attr($display_type_name . '_' . $name); ?>"
               name="<?php print esc_attr($display_type_name . '[' . $name . ']'); ?>"
               class="<?php print esc_attr($display_type_name . '_' . $name); ?>"
               value="1"
               <?php checked(True, !empty($value)); ?>/>
        <label for="<?php print esc_attr($display_type_name . '_' . $name); ?>"><?php _e('Yes'); ?></label>
        &nbsp;
        <input type="radio"
               id="<?php print esc_attr($display_type_name . '_' . $name); ?>_no"
               name="<?php print esc_attr($display_type_name . '[' . $name . ']'); ?>"
               class="<?php print esc_attr($display_type_name . '_' . $name); ?>"
               value="0"
               <?php checked(True, empty($value)); ?>/>
        <label for="<?php print esc_attr($display_type_name . '_' . $name); ?>_no"><?php _e('No'); ?></label>
    </td>
</tr>