<!DOCTYPE html>
<html>
    <head>
        <title><?php echo_h($page_title)?></title>
		<?php
			wp_print_styles();
			wp_print_scripts();
		?>
    </head>
	<body>
		<div id="attach_to_post_tabs">
            <div class='ui-tabs-icon'><br/></div>
			<ul>
			<?php foreach ($tab_titles as $title => $id): ?>
				<li>
					<a href='#<?php echo esc_attr($id)?>'>
						<?php echo_h($title) ?>
					</a>
				</li>
			<?php endforeach ?>
			</ul>
			<?php foreach ($tabs as $id => $tab_content): ?>
			<div class="main_menu_tab" id="<?php echo esc_attr($id) ?>"><?php echo $tab_content ?></div>
			<?php endforeach ?>
		</div>

		<?php wp_print_footer_scripts() ?>
	</body>
</html>
