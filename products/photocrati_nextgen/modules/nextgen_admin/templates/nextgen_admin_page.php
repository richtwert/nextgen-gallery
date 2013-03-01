<div class="wrap">
	<h2><?php echo_h($page_heading)?></h2>
	<?php if ($errors): ?>
	<?php foreach ($errors as $msg): ?>
	<?php echo $msg ?>
	<?php endforeach ?>
	<?php endif ?>
	<?php if ($success): ?>
	<div class='success'><?php echo_h($success);?></div>
	<?php endif ?>
	<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI'])?>">
		<?php
			if (isset($form_header))
			{
				echo $form_header . "\n";
			}
		?>
		<div class="accordion" id="display_settings_accordion">
		<?php foreach($tabs as $tab): ?>
			<?php echo $tab ?>
		<?php endforeach ?>
		</div>
		<p>
			<input type="submit" value="Save" class="button-primary"/>
		</p>
	</form>
</div>
