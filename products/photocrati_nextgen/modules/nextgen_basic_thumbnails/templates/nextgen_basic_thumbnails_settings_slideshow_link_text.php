<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_slideshow_link_text'>
            <?php echo_h($slideshow_link_text_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_slideshow_link_text'
               name='<?php echo esc_attr($display_type_name); ?>[slideshow_link_text]'
               class='ngg_thumbnail_slideshow_link_text'
               value='<?php echo esc_attr($slideshow_link_text); ?>'>
        </select>
    </td>
</tr>