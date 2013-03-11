<div class="wrap">
	<h2><?php echo_h($page_heading)?></h2>
	<?php if ($errors): ?>
	<?php foreach ($errors as $msg): ?>
	<?php echo $msg ?>
	<?php endforeach ?>
	<?php endif ?>
	<?php if ($success AND empty($errors)): ?>
	<div class='success updated'>
		<p><?php echo_h($success);?></p>
	</div>
	<?php endif ?>
	<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI'])?>">
		<?php if (isset($form_header)): ?>
		<?php echo $form_header."\n"; ?>
		<?php endif ?>
		<input type="hidden" name="action"/>
		<div class="accordion" id="display_settings_accordion">
		<?php foreach($tabs as $tab): ?>
			<?php echo $tab ?>
		<?php endforeach ?>
		</div>
		<p>
			<input type="submit" name='action_proxy' value="Save" class="button-primary"/>
		</p>
	</form>
</div>
