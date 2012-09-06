<script type="text/x-handlebars">
<?php foreach ($display_types as $display_type): ?>
<div class="display_type_preview">
	<div class="image_container">
		<div>
			{{view Ember.RadioButton
				selectionBinding="NggDisplayTab.displayed_gallery.display_type"
				value="<?php echo esc_attr($display_type->name)?>"
				name="display_type"
			}}
			<?php echo_h($display_type->title)?>
		</div>
		<img
			src="<?php echo esc_url(real_site_url($display_type->preview_image_relpath))?>"
			title="<?php echo esc_attr($display_type->title) ?>"
			alt="<?php echo esc_attr($display_type->title) ?>"
		/>
	</div>
</div>
<?php endforeach ?>
</script>