<table class="thumbnail_options">
	<tr>
		<td>
			<label for="thumbnail_dimensions_width">
				<?php echo_h($thumbnail_dimensions_label) ?>
			</label>
		</td>
		<td colspan="2">
		<?php
		  $thumbnails_template_width_value = $thumbnail_dimensions_width;
		  $thumbnails_template_height_value = $thumbnail_dimensions_height;
		  $thumbnails_template_width_id = 'thumbnail_dimensions_width';
		  $thumbnails_template_height_id = 'thumbnail_dimensions_height';
		  $thumbnails_template_width_name = 'thumbnail_settings[thumbwidth]';
		  $thumbnails_template_height_name = 'thumbnail_settings[thumbheight]';
		  include(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array('admin', 'thumbnails-template.php'))));
		?>
			<p class="description"><?php echo_h($thumbnail_dimensions_help)?></p>
		</td>
	</tr>
	<tr>
		<td>
			<label for="thumbnail_quality">
				<?php echo_h($thumbnail_quality_label) ?>
			</label>
		</td>
		<td colspan="2">
			<select name="thumbnail_settings[thumbquality]" id="thumbnail_quality">
			<?php for($i=100; $i>50; $i--): ?>
				<option
					<?php selected($i, $thumbnail_quality) ?>
					value="<?php echo_h($i)?>"><?php echo_h($i) ?>%</option>
			<?php endfor ?>
			</select>
			<p class="description"><?php echo_h($thumbnail_quality_help)?></p>
		</td>
	</tr>
</table>
