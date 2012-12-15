<?php

class Hook_Wordpress_URI_Params_Modifier extends Hook
{

    public function add_parameter($name, $val, $prefix = NULL, $uri = NULL)
    {
        global $wp_rewrite;

        $prop = ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE;
        $string = $this->object->get_method_property('add_parameter', $prop);

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

        // method, property, value
        $this->object->set_method_property(
            'add_parameter',
            $prop,
            $string
        );
    }

}