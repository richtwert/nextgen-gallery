<div class="wrap">
	<h2><?php echo_h($page_heading) ?></h2>
	<?php if (isset($message)): ?>
	<?php echo $message ?>
	<?php endif ?>
	<form id='nextgen_other_options' method="POST" action="<?php echo $_SERVER['REQUEST_URI']?>">
		<?php
			if (isset($form_header))
			{
				echo $form_header . "\n";
			}
		?>	
		<div id="options_accordion" class="accordion">
			<?php foreach ($tabs as $tab_title => $tab_content): ?>
			<h3><a href="#"><?php echo_h($tab_title)?></a></h3>
			<div><?php echo $tab_content ?></div>
			<?php endforeach ?>
		</div>
		<p><input type="submit" value="Save" class="button-primary"/></p>
	</form>
</div>
