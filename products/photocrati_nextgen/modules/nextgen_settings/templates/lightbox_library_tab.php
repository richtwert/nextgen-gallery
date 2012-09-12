<table>
	<!-- Lightbox Library Name -->
	<tr>
		<td class="column1">
			<label for="lightbox_library"><?php echo_h($lightbox_library_label)?></label>
		</td>
		<td>
			<select name="lightbox_library_id" id="lightbox_library">
				<?php foreach ($libs as $lib): ?>
				<option
					css_stylesheets="<?php echo esc_attr($lib->css_stylesheets)?>"
					scripts="<?php echo esc_attr($lib->scripts)?>"
					code="<?php echo esc_attr($lib->code)?>"
					value="<?php echo esc_attr($lib->$id_field)?>"
					<?php selected($lib->name, $selected, TRUE)?>>
					<?php echo_h($lib->name)?>
				</option>
				<?php endforeach ?>
			</select>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<a
				href="#"
				class="nextgen_advanced_toggle_link"
				rel="lightbox_library_advanced_settings"
				id="lightbox_library_advanced_toggle"
				hidden_label="<?php echo esc_attr(_('(Show Advanced Settings)'))?>"
				active_label="<?php echo esc_attr(_('(Hide Advanced Settings)'))?>"
				>
				<?php echo_h(_("(Show Advanced Settings)"))?>
			</a>
		</td>
	</tr>
	<tbody class="hidden" id="lightbox_library_advanced_settings">
		<!-- Lightbox Library Code -->
		<tr>
			<td>
				<label for="lightbox_library_code">
					<?php echo_h(_('Code:'))?>
				</label>
			</td>
			<td>
				<input
					type="text"
					name="lightbox_library[code]"
					id="lightbox_library_code"
				/>
			</td>
		</tr>

		<!-- Lightbox Library Stylesheets -->
		<tr>
			<td>
				<label for="lightbox_library_stylesheets">
					<?php echo_h(_("Stylesheet URLs"))?>
				</label>
			</td>
			<td>
				<textarea
					name="lightbox_library[css_stylesheets]"
					id="lightbox_library_stylesheets"
				></textarea>
			</td>
		</tr>

		<!-- Lightbox Library Scripts -->
		<tr>
			<td>
				<label for="lightbox_library_scripts">
					<?php echo_h(_("JavaScript URLs:"))?>
				</label>
			</td>
			<td>
				<textarea
					name="lightbox_library[scripts]"
					id="lightbox_library_scripts"
					>
				</textarea>
			</td>
		</tr>
	</tbody>
</table>