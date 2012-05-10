<?php

class Mixin_Substitute_Gallery_Placeholders extends Mixin
{
    /**
     * Substitutes placeholder images with gallery instances
     * @param type $posts
     * @param type $query
     * @return type
     */
    function load_attached_galleries($posts, $query)
    {
        // Only load gallery instance outside of wp-admin
        if (!is_admin()) {

            // Iterate through each post and load gallery instances
            foreach ($posts as $post) {
                $this->substitute_placeholder_imgs($post);
            }
        }

        return $posts;
    }


    /**
     * Substitutes the gallery placeholder content with the gallery type frontend
     * view, returns a list of static resources that need to be loaded
     * @param stdClass $post
     */
    function substitute_placeholder_imgs(&$post)
    {
        $found = FALSE;

        // Load html into parser
        $doc = new simple_html_dom();
		if (isset($post->post_content)) {
			$doc->load($post->post_content);

			// Find all placeholder images
			$imgs = $doc->find("img[class='nggallery_stub']");
			if ($imgs) {
				$found = TRUE;

				// Needed to simulate that we're in the content of the post
				$GLOBALS['post'] = $post;
			}

			// Substitute each image for the gallery type frontent content
			foreach ($imgs as $img) {

				// The placeholder MUST have a gallery instance id
				if (preg_match("/attached_gallery_id=([^&]+)/", $img->src, $match)) {
					$attached_gallery_id = $match[1];

					// Instantiate factory
					$factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory');

					// Create gallery instance
					$attached_gallery = $factory->create('attached_gallery');
					$attached_gallery = $attached_gallery->find($attached_gallery_id);

					// Create public view controller
					$controller = $factory->create(
						'gallery_type_controller',
						$attached_gallery->gallery_type
					);

					// Override config with attached gallery
					$controller->config = $attached_gallery;

					// Clean up
					unset($factory);

					// Is the gallery type registered?
					if ($controller) {

						// Enqueue gallery specific styles and scripts
						if ($controller->has_method('enqueue_scripts')) {
						$controller->enqueue_scripts($attached_gallery);
						}
						if ($controller->has_method('enqueue_stylesheets')) {
						$controller->enqueue_stylesheets($attached_gallery);
						}

						// Buffer controller action to get view
						ob_start();
						$controller->index();

						// Remove all whitespace so that wpautop doesn't screw up the
						// display
						echo($img->outertext = $this->compress_html(ob_get_contents()));
						ob_end_clean();
					}

					// The gallery type is no longer registered
					else {
						$img->outertext = "<p class='invalid_gallery_type'>".
							h(_($attached_gallery->gallery_type.
							" is not a valid gallery type. Perhaps it was
								uninstalled?")).
							"</p>";
					}
				}
			}

			// If gallery instances were found, then return the new HTML
			if ($found) {
				$post->post_content = (string)$doc->save();
				unset($GLOBALS['post']);
			}
		}
    }

    //  this function gets rid of tabs, line breaks, and white space
    function compress_html($html)
    {
        $html = preg_replace("/>\s+/", ">", $html);
        $html = preg_replace("/\s+</", "<", $html);
        $html = preg_replace("/<!--(?:(?!-->).)*-->/m", "", $html);
        return $html;
    }
}