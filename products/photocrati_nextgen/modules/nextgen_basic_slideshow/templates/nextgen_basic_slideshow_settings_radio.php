<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
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
               value=""
               <?php if ($attr) { foreach ($attr as $name => $val) { ?>
                   <?php print $name . "='" . $val . "'\n"; ?>
               <?php }} ?>
               <?php checked('', $value); ?>/>

        <label for="<?php print $display_type_name . '_' . $name; ?>_no"><?php _e('No'); ?></label>

        <?php if (!is_null($text)) { ?>
            <br/><?php print $text; ?>
        <?php } ?>
    </td>
</tr>
