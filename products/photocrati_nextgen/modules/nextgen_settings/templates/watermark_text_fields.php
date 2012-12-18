<tr>
	<td class="column1 align-to-top">
		<label for="watermark_text">
			<?php echo_h($watermark_text_label)?>
		</label>
	</td>
	<td>
		<textarea name="settings[wmText]" id="watermark_text"><?php echo_h($watermark_text)?></textarea>
	</td>
</tr>

<tr>
	<td>
		<label for="watermark_opacity">
			<?php echo_h($opacity_label)?>
		</label>
	</td>
	<td>
		<select name="settings[wmOpaque]" id="watermark_opacity">
		<?php for ($i=200; $i>1; $i--): ?>
			<option <?php selected($i, $opacity)?>>
				<?php echo_h($i)?>
			</option>
		<?php endfor ?>
		</select>%
	</td>
</tr>

<tr>
	<td class="column1">
		<label for="font_family">
			<?php echo_h($font_family_label); ?>
		</label>
	</td>
	<td>
		<select id="font_family" name="settings[wmFont]">
		<?php foreach ($fonts as $font): ?>
			<option <?php selected($font, $font_family); ?>>
				<?php echo_h($font); ?>
			</option>
		<?php endforeach ?>
		</select>
	</td>
</tr>

<tr>
	<td>
		<label for="watermark_font_size">
			<?php echo_h($font_size_label)?>
		</label>
	</td>
	<td>
		<select name="settings[wmSize]" id="watermark_font_size">
            <?php for($i=0; $i<200; $i++) { ?>
                <option <?php selected($i, (int)$font_size) ?>><?php echo_h($i)?></option>
            <?php } ?>
		</select>px
	</td>
</tr>

<tr>
	<td class="align-to-top">
		<label for="font_color">
			<?php echo_h($font_color_label)?>
		</label>
	</td>
	<td>
        <div id="watermark_colorpicker"><?php echo_h($font_color); ?></div>
		<input type="text"
               id="font_color"
               value="<?php echo esc_attr($font_color)?>"
               name="settings[wmColor]"
               class='nextgen_settings_colorpicker'/>
	</td>
</tr>