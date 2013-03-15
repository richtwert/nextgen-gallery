<table class="size_options">
	<tr>
		<td>
			<label for="size_list">
				<?php echo_h($size_list_label) ?>
			</label>
		</td>
		<td colspan="2">
		<?php
			if ($size_list != null && is_array($size_list))
			{
		?>
			<select class="select2 thumbnail_dimensions" name="size_settings[thumbnail_dimensions][]" id="thumbnail_dimensions" multiple="multiple">
			<?php
				foreach ($size_list as $size)
				{
			?>
				<option
					<?php selected($size, $size) ?>
					value="<?php echo_h($size)?>"><?php echo_h($size) ?></option>
			<?php
				}
			?>
			</select>
		<?php
			}
			else
			{
				echo "<i>No default sizes present.</i>";
			}
		?>
			<p class="description"><?php echo_h($size_list_help)?></p>
		</td>
	</tr>
</table>
