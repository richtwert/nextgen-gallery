<script type="text/x-handlebars" data-template-name="<?php echo esc_attr($template_name)?>">
		<tr>
			<td>
				<label for="existing_galleries">
					<?php echo_h($existing_galleries_label)?>
				</label>
			</td>
			<td>
				{{view Ember.Select
					viewName="select"
					contentBinding="NggDisplayTab.galleries"
					selectionBinding="NggDisplayTab.attached_gallery.container_ids"
					optionLabelPath="content.title"
					optionValuePath="content.id"
					multiple="multiple"
					class="pretty-dropdown"
					id="existing_galleries"
				}}
			</td>
		</tr>
</script>