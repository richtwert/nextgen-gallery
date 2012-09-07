<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="<?php print (($type == 'checkbox') ? 'true' : $value); ?>"
               <?php if ($type == 'checkbox') { print checked($value, true, false); } ?>
        />
        <?php if (!is_null($text)) { ?>
            <?php print $text; ?>
        <?php } ?>
    </td>
</tr>
