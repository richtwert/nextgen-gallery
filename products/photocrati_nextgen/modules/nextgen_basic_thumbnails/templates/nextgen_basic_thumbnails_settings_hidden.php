<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'>
            <?php echo_h($show_all_in_lightbox_label); ?>
        </label>
    </td>
    <td>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_show_all_in_lightbox'
               name='<?php echo esc_attr($display_type_name); ?>[show_all_in_lightbox]'
               class='ngg_thumbnail_show_all_in_lightbox'
               value='true'
               <?php echo checked($show_all_in_lightbox); ?>'>
        <?php echo_h($show_all_in_lightbox_desc); ?>
    </td>
</tr>
