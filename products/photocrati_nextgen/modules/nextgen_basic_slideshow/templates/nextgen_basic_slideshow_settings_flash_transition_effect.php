<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <select id="<?php print $display_type_name . '_' . $name; ?>"
                name="<?php print $display_type_name . '[' . $name . ']'; ?>"
                class="<?php print $display_type_name . '_' . $name; ?>"
            <option value="fade" <?php print selected('fade', $value, false) ; ?>><?php print __('fade', 'nggallery') ; ?></option>
            <option value="bgfade" <?php print selected('bgfade', $value, false) ; ?>><?php print __('bgfade', 'nggallery') ; ?></option>
            <option value="slowfade" <?php print selected('slowfade', $value, false); ?>><?php print __('slowfade', 'nggallery') ; ?></option>
            <option value="circles" <?php print selected('circles', $value, false) ; ?>><?php print __('circles', 'nggallery') ; ?></option>
            <option value="bubbles" <?php print selected('bubbles', $value, false) ; ?>><?php print __('bubbles', 'nggallery') ; ?></option>
            <option value="blocks" <?php print selected('blocks', $value, false) ; ?>><?php print __('blocks', 'nggallery') ; ?></option>
            <option value="fluids" <?php print selected('fluids', $value, false) ; ?>><?php print __('fluids', 'nggallery') ; ?></option>
            <option value="flash" <?php print selected('flash', $value, false) ; ?>><?php print __('flash', 'nggallery') ; ?></option>
            <option value="lines" <?php print selected('lines', $value, false) ; ?>><?php print __('lines', 'nggallery') ; ?></option>
            <option value="random" <?php print selected('random', $value, false) ; ?>><?php print __('random', 'nggallery') ; ?></option>
    </td>
</tr>
