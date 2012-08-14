<table>
	<tr>
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
		<td class="column1">
			<label for="image_sorting_direction">
				<?php echo_h($sorting_direction_label) ?>
			</label>
		</td>
		<td>
			<select name="settings[galSortDir]" id="image_sorting_direction">
				<?php foreach ($sorting_direction_options as $label => $value): ?>
				<option value="<?php echo esc_attr($value) ?>" <?php selected($value, $sorting_direction)?>>
					<?php echo_h($label) ?>
				</option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>
</table>