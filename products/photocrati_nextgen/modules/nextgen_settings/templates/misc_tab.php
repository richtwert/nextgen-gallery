<table>
	<tr>
		<td class="column1">
			<label for="mediarss_activated">
				<?php echo_h($mediarss_activated_label)?>
			</label>
		</td>
		<td>
			<label for="mediarss_activated">
				<?php echo_h($mediarss_activated_yes) ?>
			</label>
			<input
                id='mediarss_activated'
				type="radio"
				name="settings[useMediaRSS]"
				value="1"
				<?php checked(TRUE, $mediarss_activated ? TRUE : FALSE)?>
			/>
			&nbsp;
			<label for="mediarss_activated_no">
				<?php echo_h($mediarss_activated_no) ?>
			</label>
			<input
                id='mediarss_activated_no'
				type="radio"
				name="settings[useMediaRSS]"
				value="0"
				<?php checked(FALSE, $mediarss_activated ? TRUE : FALSE)?>
			/>
			<p class="description">
				<?php echo_h($mediarss_activated_help)?>
			</p>
		</td>
	</tr>
</table>
