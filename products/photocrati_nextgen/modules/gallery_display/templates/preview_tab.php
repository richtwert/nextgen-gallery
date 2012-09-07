<script type="text/x-handlebars" data-template-name="preview_area">
<ul id="preview_entity_list">
	<li class="previewed_entity header">
		{{view ExcludeAllButton id="exclude_toggle_all"}}
		<label for="exclude_toggle_all">
			<?php echo_h($exclude_all_label)?>
		</label>
	</li>
	{{#each entity in entities}}
	<li class="previewed_entity">
		<div class="container">
			{{view ExcludeButton class="inclusion_checkbox" valueBinding="entity.id"}}
			<div class="image_container">
				<img
					{{bindAttr src="entity.thumb_url" alt="entity.alttext" title="entity.title" width="entity.thumb_size.width" height="entity.thumb_size.height"}}
				/>
			</div>
		</div>
		<br class="clear"/>
	</li>
	{{/each}}
</ul>
</script>

<script type="text/x-handlebars" data-template-name="no_entities_available">
	<p>There is nothing to preview</p>
</script>

<script type="text/x-handlebars" data-template-name="preview_not_supported">
	<p>A preview cannot be generated for this source.</p>
</script>