<tr id='tr_<?php print esc_attr("{$display_type_name}_{$name}"); ?>' class='<?php print !empty($hidden) ? 'hidden' : ''; ?>'>
    <td colspan='2'>
        <div id="<?php print "{$display_type_name}_{$name}"; ?>_colorpicker"
             class="nextgen_settings_farbtastic"
             data-nextgen-settings-farbtastic-target="<?php print "{$display_type_name}_{$name}"; ?>">
            <?php echo_h($value); ?>
        </div>
        <table>
            <tr>
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
                           class="<?php print esc_attr("{$display_type_name}_{$name}"); ?> nextgen_settings_colorpicker"
                           value="<?php print esc_attr($value); ?>"/>
                </td>
            </tr>
        </table>
    </td>
</tr>