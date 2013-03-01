<tr id='tr_<?php print esc_attr("{$display_type_name}_{$name}"); ?>' class='<?php print !empty($hidden) ? 'hidden' : ''; ?>'>
    <td>
        <label for="<?php print esc_attr($display_type_name . '_' . $name); ?>"
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
        <?php
        $thumbnails_template_width_value = $thumbnail_width;
        $thumbnails_template_height_value = $thumbnail_height;
        $thumbnails_template_id = $display_type_name . '_thumbnail_dimensions';
        $thumbnails_template_width_id = $display_type_name . '_thumbnail_width';
        $thumbnails_template_height_id = $display_type_name . '_thumbnail_height';
        $thumbnails_template_name = $display_type_name . '_thumbnail_dimensions';
        $thumbnails_template_width_name = $display_type_name . '[thumbnail_width]';
        $thumbnails_template_height_name = $display_type_name . '[thumbnail_height]';
        include(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array('admin', 'thumbnails-template.php'))));
        ?>
    </td>
</tr>