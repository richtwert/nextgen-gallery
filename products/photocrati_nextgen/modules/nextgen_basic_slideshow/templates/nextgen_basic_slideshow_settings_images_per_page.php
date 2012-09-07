<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_images_per_page'>
            <?php echo_h($images_per_page_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_images_per_page'
               name='<?php echo esc_attr($display_type_name); ?>[images_per_page]'
               class='ngg_thumbnail_images_per_page'
               value='<?php echo esc_attr($images_per_page); ?>' />
    </td>
</tr>