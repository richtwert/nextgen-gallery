<table>
	<tr>
		<td class="column1">
			<label for="watermark_source">
				<?php echo_h($watermark_source_label)?>
			</label>
		</td>
		<td>
			<div class="column_wrapper">
				<select name="settings[wmType]" id="watermark_source">
				<?php foreach ($watermark_sources as $label => $value): ?>
					<option
						value="<?php echo esc_attr($value)?>"
						<?php selected($value, $watermark_source) ?>
						><?php echo_h($label)?></option>
				<?php endforeach ?>
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<a
				id="watermark_customization"
				href="#"
				class="advanced_toggle_link"
				hidden_label="<?php echo esc_attr($hidden_label)?>"
				active_label="<?php echo esc_attr($active_label)?>"
			>
			<?php echo_h($hidden_label)?>
			</a>
		</td>
	</tr>
	<?php foreach ($watermark_fields as $source_name => $fields): ?>
	<tbody class="hidden" id="watermark_<?php echo esc_attr($source_name) ?>_source">
		<?php echo $fields ?>
	</tbody>
	<?php endforeach ?>
</table>