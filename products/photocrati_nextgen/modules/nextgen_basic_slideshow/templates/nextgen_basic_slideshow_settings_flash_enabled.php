<tr>
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <input type="radio"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="1"
               <?php checked(TRUE, $value); ?>/>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php _e('Yes'); ?></label>
        &nbsp;
        <input type="radio"
               id="<?php print $display_type_name . '_' . $name; ?>_no"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value=""
               <?php checked(FALSE, $value); ?>/>
        <label for="<?php print $display_type_name . '_' . $name; ?>_no"><?php _e('No'); ?></label>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("input[name='<?php print $display_type_name . '[' . $name . ']'; ?>']").click(function() {
                    $("tr.nextgen-settings-slideshow-flash").toggle('slow');
                });
            });
        </script>
    </td>
</tr>
