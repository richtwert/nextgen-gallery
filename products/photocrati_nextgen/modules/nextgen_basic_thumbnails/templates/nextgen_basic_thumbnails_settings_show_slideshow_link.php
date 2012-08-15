<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'>
            <?php echo_h($show_slideshow_link_label); ?>
        </label>
    </td>
    <td>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'
               name='<?php echo esc_attr($display_type_name); ?>[show_slideshow_link]'
               class='ngg_thumbnail_show_slideshow_link'
               value='true'
                <?php echo checked($show_slideshow_link); ?>'>
        </select>
    </td>
</tr>
