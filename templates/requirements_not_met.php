<div id="message" class="error">
	<h3><?php _e('NextGEN Gallery Requirements Not Met', PHOTOCRATI_GALLERY_I8N_DOMAIN) ?></h3>
	<p>
		<ul>
			<li><?php print_f(__('WordPress %s or higher', $this->minimum_wordpress_version)) ?></li>
			<li><?php print_f(__('PHP Memory limit of %dMB or higher', $this->$minimum_memory_limit)) ?></li>
		</ul>
	</p>
</div>