<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_piclens_link_text'>
            <?php echo_h($piclens_link_text_label); ?>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_piclens_link_text'
               name='<?php echo esc_attr($display_type_name); ?>[piclens_link_text]'
               class='ngg_thumbnail_piclens_link_text'
               placeholder='<?php _e('link text'); ?>'
               value='<?php echo esc_attr($piclens_link_text); ?>'/>
    </td>
</tr>
