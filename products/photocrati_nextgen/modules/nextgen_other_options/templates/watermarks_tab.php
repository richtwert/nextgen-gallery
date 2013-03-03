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
        <td>
            <?php echo $position_label; ?>
        </td>
        <td>
            <table class='nextgen_settings_position' border='1'>
                <tr>
                    <td><input type="radio" name="settings[wmPos]" value="topLeft"   <?php checked('topLeft',   $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="topCenter" <?php checked('topCenter', $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="topRight"  <?php checked('topRight',  $position); ?>/></td>
                </tr>
                <tr>
                    <td><input type="radio" name="settings[wmPos]" value="midLeft"   <?php checked('midLeft',   $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="midCenter" <?php checked('midCenter', $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="midRight"  <?php checked('midRight',  $position); ?>/></td>
                </tr>
                <tr>
                    <td><input type="radio" name="settings[wmPos]" value="botLeft"   <?php checked('botLeft',   $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="botCenter" <?php checked('botCenter', $position); ?>/></td>
                    <td><input type="radio" name="settings[wmPos]" value="botRight"  <?php checked('botRight',  $position); ?>/></td>
                </tr>
            </table>
        </td
    </tr>

    <tr>
        <td>
            <?php echo $offset_label; ?>
        </td>
        <td>
            <label for='nextgen_settings_wmXpos'>w</label>
            <input type='number'
                   id='nextgen_settings_wmXpos'
                   name='settings[wmXpos]'
                   placeholder='0'
                   min='0'
                   value='<?php echo $offset_x; ?>'/> /
            <input type='number'
                   id='nextgen_settings_wmYpos'
                   name='settings[wmYpos]'
                   placeholder='0'
                   min='0'
                   value='<?php echo $offset_y; ?>'/>
            <label for='nextgen_settings_wmYpos'>h</label>
        </td>
    </tr>

    <?php if (!is_null($thumbnail_url)) { ?>
        <tr>
            <td>
                <?php echo $preview_label; ?>
            </td>
            <td>
                <img src='<?php echo $thumbnail_url; ?>'/>
                <button id='nextgen_settings_preview_refresh' data-refresh-url='<?php echo $refresh_url; ?>'><?php echo $refresh_label; ?></button>
            </td>
        </tr>
    <?php } ?>

	<tr>
		<td colspan="2">
			<a
				id="watermark_customization"
				href="#"
				class="nextgen_advanced_toggle_link"
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
