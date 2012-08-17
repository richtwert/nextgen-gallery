<table class="image_options">
	<tr>
		<td class="column1">
			<label for="gallery_path">
				<?php echo_h($gallery_path_label)?>
			</label>
		</td>
		<td colspan="2">
			<input
				id="gallery_path"
				type="text"
				name="settings[galleryPath]"
				value="<?php echo esc_attr($gallery_path) ?>"
			/>
			<p class="description">
				<?php echo_h($gallery_path_help)?>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<label for="delete_images">
				<?php echo_h($delete_image_files_label) ?>
			</label>
		</td>
		<td colspan="2">
			<p class="description">
				<input
					type="checkbox"
					id="delete_images"
					name="settings[deleteImg]"
					value="1"
					<?php checked(TRUE, $delete_image_files? TRUE: FALSE)?>
				/>
				<?php echo_h($delete_image_files_help)?>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<label for="automatic_resize">
				<?php echo_h($automatic_resize_label)?>
			</label>
		</td>
		<td colspan="2">
			<p class="description">
				<input
					type="checkbox"
					id="automatic_resize"
					name="settings[imgAutoResize]"
					value="1"
					<?php checked(TRUE, $automatic_resize ? TRUE : FALSE)?>
				/>
				<?php echo_h($automatic_resize_help) ?>
			</p>

		</td>
	</tr>
	<tr>
		<td>
			<label for="resize_images">
				<?php echo_h($resize_images_label) ?>
			</label>
		</td>
		<td class="column2">
			<label for="image_width"><?php echo_h($resized_image_width_label)?></label>
			<input
				type="text"
				id="image_width"
				maxlength="4"
				name="settings[imgWidth]"
				value="<?php echo esc_attr($resized_image_width) ?>"
			/>
			&nbsp;<label for="image_height"><?php echo_h($resized_image_height_label)?></label>
			<input
				type="text"
				id="image_height"
				maxlength="4"
				name="settings[imgHeight]"
				value="<?php echo esc_attr($resized_image_height) ?>"
			/>
		</td>
		<td>
			<div class="column_wrapper">
				<label for="image_quality"><?php echo_h($resized_image_quality_label)?></label>
				<select id="image_quality" name="settings[imgQuality]">
				<?php for($i=100; $i>50; $i--): ?>
					<option
						<?php selected($i, $resized_image_quality) ?>
						value="<?php echo_h($i)?>"><?php echo_h($i) ?>%</option>
				<?php endfor ?>
				</select>
			</div>
		</td>
	</tr>
	<tr class="description_row">
		<td class="column1"></td>
		<td colspan="2">
			<p class="description"><?php echo_h($resize_images_help) ?></p>
		</td>
	</tr>
	<tr>
		<td>
			<label for="backup_images">
				<?php echo_h($backup_images_label)?>
			</label>
		</td>
		<td colspan="2">
			<label for="backup_images_yes">
				<?php echo_h($backup_images_yes_label)?>
			</label>
			<input
				id="backup_images_yes"
				name="settings[backup_images]"
				value="1"
				type="radio"
				<?php checked(1, $backup_images)?>
			/>
			&nbsp;
			<label for="backup_images_no">
				<?php echo_h($backup_images_no_label)?>
			</label>
			<input
				id="backup_images_no"
				name="settings[backup_images]"
				value="0"
				type="radio"
				<?php checked(0, $backup_images)?>
			/>
		</td>
	</tr>
	<tr>
		<td>
			<label for="thumbnail_quality">
				<?php echo_h($thumbnail_quality_label) ?>
			</label>
		</td>
		<td colspan="2">
			<select name="settings[thumbquality]" id="thumbnail_quality">
			<?php for($i=100; $i>50; $i--): ?>
				<option
					<?php selected($i, $thumbnail_quality) ?>
					value="<?php echo_h($i)?>"><?php echo_h($i) ?>%</option>
			<?php endfor ?>
			</select>
			<p class="description"><?php echo_h($thumbnail_quality_help)?></p>
		</td>
	</tr>
	<tr id="sorting_options_row">
		<td class="column1">
			<label for="image_sorting_order">
				<?php echo_h($sorting_order_label) ?>
			</label>
		</td>
		<td>
			<select name="settings[galSort]" id="image_sorting_order">
				<?php foreach ($sorting_order_options as $label => $value): ?>
				<option value="<?php echo esc_attr($value) ?>" <?php selected($value, $sorting_order)?>>
					<?php echo_h($label) ?>
				</option>
				<?php endforeach ?>
			</select>
		</td>
		<td class="column3">
			<label for="image_sorting_direction">
				<?php echo_h($sorting_direction_label) ?>
			</label>
			<select name="settings[galSortDir]" id="image_sorting_direction">
			<?php foreach ($sorting_direction_options as $label => $value): ?>
			<option value="<?php echo esc_attr($value) ?>" <?php selected($value, $sorting_direction)?>>
				<?php echo_h($label) ?>
			</option>
			<?php endforeach ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label for="show_related_images">
				<?php echo_h($show_related_images_label)?>
			</label>
		</td>
		<td colspan="2">
			<p class="description">
				<input
					type="checkbox"
					id="show_related_images"
					name="settings[activateTags]"
					value="1"
					<?php checked(TRUE, $show_related_images ? TRUE: FALSE)?>
				/>
				<?php echo_h($show_related_images_help) ?>
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<a
				href="#"
				class="advanced_toggle_link"
				id="related_images_advanced_toggle"
				rel="related_images_advanced_form"
				hidden_label="<?php echo esc_attr($related_images_hidden_label)?>"
				active_label="<?php echo esc_attr($related_images_active_label)?>"
			><?php echo_h($related_images_hidden_label) ?></a>
		</td>
	</tr>
	<tr id="related_images_advanced_form" class="hidden">
		<td>
			<label for="match_related_images">
				<?php echo_h($match_related_images_label) ?>
			</label>
		</td>
		<td>
			<select id="match_related_images" name="settings[appendType]">
			<?php foreach ($match_related_image_options as $label => $value): ?>
				<option
					value="<?php echo esc_attr($value)?>"
					<?php selected($value, $match_related_images)?>
				>
				<?php echo_h($label) ?>
				</option>
			<?php endforeach ?>
			</select>
		</td>
		<td class="column3">
			<label for="max_related_images">
				<?php echo_h($max_related_images_label)?>
			</label>
			<input
				id="max_related_images"
				type="text"
				name="settings[maxImages]"
				value="<?php echo esc_attr($max_related_images)?>"
			/>
		</td>
	</tr>
</table>