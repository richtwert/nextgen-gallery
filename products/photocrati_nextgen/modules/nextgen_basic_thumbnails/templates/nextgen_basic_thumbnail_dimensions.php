<tr>
	<td>
		<label for="<?php echo esc_attr($display_type_name) ?>_width">
			<?php echo_h($thumbnail_width_label) ?>
		</label>
	</td>
	<td>
		<input
			type="text"
			name="<?php echo esc_attr($display_type_name) ?>[thumbnail_width]"
			id="<?php echo esc_attr($display_type_name) ?>_width"
			value="<?php echo esc_attr($thumbnail_width) ?>"
		/>
	</td>
	<td>
		<label for="<?php echo esc_attr($display_type_name) ?>_height">
			<?php echo_h($thumbnail_height_label) ?>
		</label>
	</td>
	<td>
		<input
			type="text"
			name="<?php echo esc_attr($display_type_name) ?>[thumbnail_height]"
			id="<?php echo esc_attr($display_type_name) ?>_height"
			value="<?php echo esc_attr($thumbnail_height) ?>"
		/>
	</td>
</tr>