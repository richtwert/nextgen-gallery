<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <select id="<?php print $display_type_name . '_' . $name; ?>"
                name="<?php print $display_type_name . '[' . $name . ']'; ?>"
                class="<?php print $display_type_name . '_' . $name; ?>"
                size="1">
            <option value="true" <?php print selected('true', $value, false); ?>><?php print __('true', 'nggallery'); ?></option>
            <option value="false" <?php print selected('false', $value, false); ?>><?php print __('false', 'nggallery');?></option>
            <option value="fit" <?php print selected('fit', $value, false); ?>><?php print __('fit', 'nggallery'); ?></option>
            <option value="none" <?php print selected('none', $value, false); ?>><?php print __('none', 'nggallery'); ?></option>
        </select>
    </td>
</tr>
