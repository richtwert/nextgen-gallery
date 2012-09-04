<?php if ($messages): ?>
<?php foreach ($messages as $msg): ?>
<?php echo $msg ?>
<?php endforeach ?>
<?php endif ?>
<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI'])?>">
	<div class="accordion" id="display_settings_accordion">
	<?php foreach($tabs as $tab): ?>
		<?php echo $tab ?>
	<?php endforeach ?>
	</div>
	<p>
		<script type="text/x-handlebars">
			<input
				type="submit"
				value="Save"
				class="button-primary"
				{{action "save" target="NggDisplayTab" on="click"}}
			/>
		</script>
	</p>
</form>