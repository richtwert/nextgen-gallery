<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_return_link_text' class='tooltip'>
            <?php echo_h($return_link_text_label); ?>
			<span>
				<?php echo_h($tooltip)?>
			</span>
        </label>
    </td>
    <td>
        <input type='text'
               id='<?php echo esc_attr($display_type_name); ?>_return_link_text'
               name='<?php echo esc_attr($display_type_name); ?>[return_link_text]'
               class='ngg_thumbnail_return_link_text'
               placeholder='<?php _e('link text'); ?>'
               value='<?php echo esc_attr($return_link_text); ?>'>
    </td>
</tr>