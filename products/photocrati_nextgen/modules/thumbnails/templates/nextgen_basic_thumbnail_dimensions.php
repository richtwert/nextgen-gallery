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
    <td></td>
	<td>
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
        <td>
            <?php echo_h($thumbnail_dimensions_label) ?>
        </td>
		<td>
            <p class="description">
                You can add a new thumbnail size by specifying a custom width/height.
            </p>
            w
			<input
				type="number"
				name="<?php echo esc_attr($display_type_name) ?>[thumbnail_width]"
				id="<?php echo esc_attr($display_type_name) ?>_width"
				rel="<?php echo esc_attr($display_type_name)?>_dimensions"
				class="ngg_thumbnail_dimension_width"
                placeholder='<?php echo_h($thumbnail_width_label) ?>'
                min='1'
				value="<?php echo esc_attr($thumbnail_width) ?>"
			/>
            /
			<input
				type="number"
				name="<?php echo esc_attr($display_type_name) ?>[thumbnail_height]"
				id="<?php echo esc_attr($display_type_name) ?>_height"
				rel="<?php echo esc_attr($display_type_name)?>_dimensions"
				class="ngg_thumbnail_dimension_height"
                placeholder='<?php echo_h($thumbnail_height_label) ?>'
                min='1'
				value="<?php echo esc_attr($thumbnail_height) ?>"
			/> h
		</td>
	</tr>
</tbody>
