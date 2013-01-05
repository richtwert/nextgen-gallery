<?php

class A_NextGen_Basic_Album_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'render_displayed_gallery',
			'Add late url rewriting for albums',
			__CLASS__,
			'_nextgen_basic_album_rewrite_rules'
		);
	}

	function _nextgen_basic_album_rewrite_rules($displayed_gallery)
	{
		// Get display types
		$original_display_type	= isset($displayed_gallery->display_settings['original_display_type']) ?
			$displayed_gallery->display_settings['original_display_type'] : '';
		$display_type			= $displayed_gallery->display_type;

		// Get router
		$router					= $this->object->get_registry()->get_utility('I_Router');
		$app					= $router->get_routed_app();

		// If we're viewing an album, rewrite the urls
		$regex = "/photocrati-nextgen_basic_\w+_album/";
		if (preg_match($regex, $display_type)) {
			$app->rewrite('nggallery/{\w}',					'nggallery/album--{1}');
			$app->rewrite('nggallery/{\w}/{\w}',			'nggallery/album--{1}/gallery--{2}');
			$app->rewrite('nggallery/{\w}/{\w}/{\w}',		'nggallery/album--{1}/gallery--{2}/{3}');
		}
		elseif (preg_match($regex, $original_display_type)) {
			$displayed_gallery->id(NULL);
			$app->rewrite('nggallery/album--{\w}',		'nggallery/{1}');
			$app->rewrite('nggallery/album--{\w}/gallery--{\w}', 'nggallery/{1}/{2}');
		}

		// Perform rewrites
		$app->do_rewrites();
	}
}