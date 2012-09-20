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
        <select
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>">
            <?php foreach ($options as $key => $val) { ?>
                <option value='<?php print $key; ?>' <?php selected($key, $value); ?>><?php print _($val); ?></option>
            <?php } ?>
        </select>
    </td>
</tr>
