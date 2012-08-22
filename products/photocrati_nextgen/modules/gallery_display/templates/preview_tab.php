<script type="text/x-handlebars" data-template-name="preview_area">
<div class="previewed_entity header">
	<div class="inclusion_column">
		<label for="exclude_toggle_all">
			<?php echo_h($exclude_all_label)?>
		</label>
		<input id="exclude_toggle_all" type="checkbox"/>
	</div>

	<div class="preview_column">

	</div>

	<br class="clear"/>
</div>
{{#each entities}}
<div class="previewed_entity">
	<div class="inclusion_column">
		<input type="checkbox"/>
	</div>

	<div class="preview_column">
		<div class="image_container">
			<img
				{{bindAttr src="thumb_url" alt="alttext" title="title" width="thumb_size.width" height="thumb_size.height"}}
			/>
		</div>
	</div>

	<br class="clear"/>
</div>
{{/each}}
</script>