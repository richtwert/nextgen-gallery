<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_altview_link'>
            <?php echo_h($show_return_link_label); ?>
        </label>
    </td>
    <td>
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_altview_link'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='show_altview_link'
			value='1'
			<?php echo checked(1, intval($show_return_link)); ?>'>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_altview_link'>Yes</label>
		&nbsp;
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_altview_link_no'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='show_altview_link'
			value='0'
			<?php echo checked(0, $show_return_link); ?>/>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_altview_link_no'>No</label>
    </td>
</tr>
