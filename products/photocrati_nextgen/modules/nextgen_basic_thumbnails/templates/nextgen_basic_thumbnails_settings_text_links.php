<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_slideshow_text_link'>
            <?php echo_h($slideshow_text_link_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_slideshow_text_link'
               name='<?php echo esc_attr($display_type_name); ?>[slideshow_text_link]'
               class='ngg_thumbnail_slideshow_text_link'
               value='<?php echo esc_attr($slideshow_text_link); ?>'>
        </select>
    </td>
</tr>
<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_piclens_text_link'>
            <?php echo_h($piclens_text_link_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_piclens_text_link'
               name='<?php echo esc_attr($display_type_name); ?>[piclens_text_link]'
               class='ngg_thumbnail_piclens_text_link'
               value='<?php echo esc_attr($piclens_text_link); ?>'>
        </select>
    </td>
</tr>
