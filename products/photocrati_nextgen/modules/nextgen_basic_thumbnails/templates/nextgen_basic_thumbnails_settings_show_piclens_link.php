<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_piclens_link'>
            <?php echo_h($show_piclens_link_label); ?>
        </label>
    </td>
    <td>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_show_piclens_link'
               name='<?php echo esc_attr($display_type_name); ?>[show_piclens_link]'
               class='ngg_thumbnail_show_piclens_link'
               value='true'
               <?php echo checked($show_piclens_link); ?>'>
        </select>
    </td>
</tr>
