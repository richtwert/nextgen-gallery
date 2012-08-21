<table id="source_configuration">
	<tr>
		<td>
			<label for="displayed_gallery_source">
				<?php echo_h($source_label)?>
			</label>
		</td>
		<td>
			<script type="text/x-handlebars">
				<!-- Select a source for images/galleries -->
				{{view Ember.Select
					viewName="select"
					contentBinding="NggDisplayTab.sources"
					selectionBinding="NggDisplayTab.displayed_gallery.source"
					optionLabelPath="content.title"
					optionValuePath="content.id"
					id="displayed_gallery_source"
					prompt="--Select--"
				}}
			</script>
		</td>
	</tr>
</table>

<!-- Templates for each source -->
<?php foreach ($source_templates as $source_template): ?>
	<?php echo $source_template; ?>
<?php endforeach ?>