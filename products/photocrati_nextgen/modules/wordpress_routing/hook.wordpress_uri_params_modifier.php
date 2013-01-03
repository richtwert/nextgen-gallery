<?php

class Hook_Wordpress_URI_Params_Modifier extends Hook
{
    /**
     * This is a post-hook to prefix to generated URL the attached page or post ID when the URL generation request is
     * coming from something on the front page display.
     */
    public function set_param_for()
    {
        global $wp_rewrite;

        $prop = ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE;
        $string = str_replace(site_url(), '', $this->object->get_method_property('set_param_for', $prop));

		if (!empty($wp_rewrite) && $wp_rewrite->using_permalinks())
        {
            if (did_action('posts_selection') >= 1 && is_home())
                $string = get_permalink(get_post(get_the_ID())->ID) . ltrim($string, '/');
        }
        else {
            $query = NULL;

            if (did_action('posts_selection') >= 1 && is_home())
                $query = get_permalink(get_the_ID());

            if (!is_null($query))
                $string = $query . '&' . ltrim($string, '?');
        }

        // assign our new $string value to be the value the parent add_parameter() will return
        $this->object->set_method_property('set_param_for', $prop, $string);
    }

}
