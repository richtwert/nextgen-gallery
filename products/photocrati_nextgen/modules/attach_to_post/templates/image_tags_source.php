<script type="text/x-handlebars" data-template-name="<?php echo esc_attr($template_name)?>">
		<tr>
			<td>
				<label for="existing_galleries">
					<?php echo_h($tags_label)?>
				</label>
			</td>
			<td>
				{{view Ember.Chosen
					viewName="select"
					contentBinding="NggDisplayTab.image_tags"
					selectionBinding="NggDisplayTab.displayed_gallery.containers"
					optionLabelPath="content.title"
					optionValuePath="content.id"
					multiple="multiple"
					class="pretty-dropdown"
					id="image_tags"
					fillCallback="fetch_image_tags"
				}}
			</td>
		</tr>
</script>