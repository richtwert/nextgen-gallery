<tr>
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"
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
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="1"
            <?php checked(1, $value); ?>/>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php _e('Yes'); ?></label>
        &nbsp;
        <input type="radio"
               id="<?php print $display_type_name . '_' . $name; ?>_no"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="0"
            <?php checked(0, $value); ?>/>
        <label for="<?php print $display_type_name . '_' . $name; ?>_no"><?php _e('No'); ?></label>
    </td>
</tr>