<tr>
	<td id="column1">
		<label for="<?php echo esc_attr($display_type_name) ?>_dimensions">
			<?php echo_h($thumbnail_dimensions_label) ?>
		</label>
	</td>
	<td>
		<select id="<?php echo esc_attr($display_type_name)?>_dimensions"
				class="ngg_thumbnail_dimensions"
				name="<?php echo esc_attr($display_type_name) ?>[thumbnail_dimensions]">
			<?php foreach ($thumbnail_dimensions as $dimension): ?>
			<option <?php selected($dimension, $selected_dimensions)?>>
				<?php echo_h($dimension) ?>
			</option>
			<?php endforeach ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2">
		<a
			href="#"
			id="<?php echo esc_attr($display_type_name)?>_customize_dimensions"
			class="ngg_customize_thumbnails"
			active_label="<?php echo esc_attr($active_customization_label)?>"
			hidden_label="<?php echo esc_attr($hidden_customization_label)?>"
			>
			<?php echo_h($hidden_customization_label)?>
		</a>
	</td>
</tr>
<tbody class="hidden customize_thumbnail_dimensions" rel="<?php echo esc_attr($display_type_name)?>_customize_dimensions">
	<tr>
		<td colspan="4">
			<p class="description">
				You can add a new thumbnail size by specifying a custom width/height.
			</p>
		</td>
	</tr>
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
				rel="<?php echo esc_attr($display_type_name)?>_dimensions"
				class="ngg_thumbnail_dimension_width"
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
				rel="<?php echo esc_attr($display_type_name)?>_dimensions"
				class="ngg_thumbnail_dimension_height"
				value="<?php echo esc_attr($thumbnail_height) ?>"
			/>
		</td>
	</tr>
</tbody>