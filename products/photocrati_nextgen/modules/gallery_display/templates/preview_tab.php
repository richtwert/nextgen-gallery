<script type="text/x-handlebars" data-template-name="preview_area">

<ul id="preview_entity_list">
	<li class="previewed_entity header">
		<input id="exclude_toggle_all" class="inclusion_checkbox" type="checkbox"/>

		<label for="exclude_toggle_all">
			<?php echo_h($exclude_all_label)?>
		</label>
	</li>
	{{#each entities}}
	<li class="previewed_entity">
		<div class="container">
			<input type="checkbox" class="inclusion_checkbox" />
			<div class="image_container">
				<img
					{{bindAttr src="thumb_url" alt="alttext" title="title" width="thumb_size.width" height="thumb_size.height"}}
				/>
			</div>
		</div>
		<br class="clear"/>
	</li>
	{{/each}}
</ul>
</script>