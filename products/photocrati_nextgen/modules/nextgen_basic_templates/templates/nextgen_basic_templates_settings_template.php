<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_template'>
            <?php echo_h($template_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_template'
               name='<?php echo esc_attr($display_type_name); ?>[template]'
               class='ngg_thumbnail_template'
               value='<?php echo esc_attr($template); ?>'>
        <script>
            jQuery(function($)
            {
                var availableTags = <?php print $files; ?>;
                $("#<?php echo esc_attr($display_type_name); ?>_template").autocomplete({
                    source: availableTags,
                    select: function(event, ui)
                    {
                        var re = new RegExp('^.+: (.+)');
                        var val = re.exec(ui.item.value);
                        $("#<?php echo esc_attr($display_type_name); ?>_template").val(val[1]);
                        return false;
                    }
                });
            });
        </script>
    </td>
</tr>
