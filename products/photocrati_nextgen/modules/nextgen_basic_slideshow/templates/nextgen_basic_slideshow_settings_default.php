<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="<?php print $value; ?>"
               <?php if ($attr) { foreach ($attr as $name => $val) { ?>
                   <?php print $name . "='" . $val . "'\n"; ?>
               <?php }} ?>
        />
        <?php if (!is_null($text)) { ?>
            <?php print $text; ?>
        <?php } ?>
    </td>
</tr>
