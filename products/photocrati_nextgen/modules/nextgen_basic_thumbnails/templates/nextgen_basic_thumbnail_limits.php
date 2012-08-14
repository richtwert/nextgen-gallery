<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_images_per_page'>
            <?php echo_h($thumbnail_images_per_page_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_images_per_page'
               name='<?php echo esc_attr($display_type_name); ?>[galImages]'
               class='ngg_thumbnail_images_per_page'
               value='<?php echo esc_attr($thumbnail_images_per_page); ?>'>
        </select>
    </td>
</tr>
<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_columns_per_page'>
            <?php echo_h($thumbnail_columns_per_page_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_columns_per_page'
               name='<?php echo esc_attr($display_type_name); ?>[galColumns]'
               class='ngg_thumbnail_columns_per_page'
               value='<?php echo esc_attr($thumbnail_columns_per_page); ?>'>
        </select>
    </td>
</tr>
