<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
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
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="1"
               <?php if ($attr) { foreach ($attr as $name => $val) { ?>
                   <?php print $name . "='" . $val . "'\n"; ?>
               <?php }} ?>
               <?php checked(1, $value); ?>/>

        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php _e('Yes'); ?></label>
        &nbsp;
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>_no"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="0"
               <?php if ($attr) { foreach ($attr as $name => $val) { ?>
                   <?php print $name . "='" . $val . "'\n"; ?>
               <?php }} ?>
               <?php checked(0, $value); ?>/>

        <label for="<?php print $display_type_name . '_' . $name; ?>_no"><?php _e('No'); ?></label>
    </td>
</tr>
