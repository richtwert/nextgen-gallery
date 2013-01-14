<table>
	<tr>
		<td class='column1'>
			<label class="tooltip" for="jsconsole_enabled">
				<?php echo_h($jsconsole_enabled_label); ?>
				<span><?php echo_h($jsconsole_enabled_tooltip); ?></span>
			</label>
		</td>
		<td>
			<input
				type="radio"
				id="jsconsole_enabled"
				name="settings[jsconsole_enabled]"
				value="1"
				<?php checked(TRUE, $jsconsole_enabled? TRUE: FALSE)?>
			/>
			<label for="jsconsole_enabled"><?php echo_h(_('Yes'))?></label>
			&nbsp;
			<input
				type="radio"
				id="jsconsole_enabled_no"
				name="settings[jsconsole_enabled]"
				value="0"
				<?php checked(FALSE, $jsconsole_enabled? TRUE: FALSE)?>
			/>
			<label for="jsconsole_enabled_no"><?php echo_h(_('No'))?></label>
		</td>
	</tr>
	<tr id="jsconsole_session_key_row" class="<?php if (!$jsconsole_enabled) echo 'hidden' ?>">
		<td>
			<label class="tooltip" for="jsconsole_session_key">
				<?php echo_h($jsconsole_session_key_label); ?>
				<span><?php echo_h($jsconsole_session_key_tooltip) ?></span>
			</label>
		</td>
		<td>
			<input
				type="text"
				id="jsconsole_session_key"
				name="settings[jsconsole_session_key]"
				value="<?php echo esc_attr($jsconsole_session_key)?>"
			/>
		</td>
	</tr>
</table>