<tr>
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
    </td>
    <td>
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               <?php if ($type == 'checkbox') { print checked($value, 'on', false); } ?>
        />
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("#<?php print $display_type_name . '_' . $name; ?>").click(function() {
                    $("tr.nextgen-settings-slideshow-flash").toggle('slow');
                });
            });
        </script>
    </td>
</tr>
