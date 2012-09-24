<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_return_link'>
            <?php echo_h($show_return_link_label); ?>
        </label>
    </td>
    <td>
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_return_link'
			name='<?php echo esc_attr($display_type_name); ?>[show_return_link]'
			class='ngg_slideshow_show_return_link'
			value='1'
			<?php echo checked(1, intval($show_return_link)); ?>'>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_return_link'>Yes</label>
		&nbsp;
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_return_link_no'
			name='<?php echo esc_attr($display_type_name); ?>[show_return_link]'
			class='ngg_slideshow_show_return_link'
			value='0'
			<?php echo checked(0, $show_return_link); ?>/>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_return_link_no'>No</label>
		&nbsp;Value: <?php var_dump($show_return_link)?>
    </td>
</tr>
