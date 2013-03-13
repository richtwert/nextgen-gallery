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
        <input type='text'
               id='<?php print esc_attr("{$display_type_name}_{$name}"); ?>'
               name='<?php print esc_attr("{$display_type_name}[{$name}]"); ?>'
               class='<?php print esc_attr("{$display_type_name}[{$name}]"); ?>'
               <?php if (!empty($placeholder)) { ?>placeholder='<?php print esc_attr($placeholder); ?>'<?php } ?>
               value='<?php print esc_attr($value); ?>'/>
    </td>
</tr>