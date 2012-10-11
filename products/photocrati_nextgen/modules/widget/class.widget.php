<?php

class C_Widget extends C_Component
{
    public static $_instances = array();

    function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Widget');
        $this->implement('I_Widget');
    }

    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Widget($context);
        }
        return self::$_instances[$context];
    }
}

class Mixin_Widget extends Mixin
{
    /**
     * nggSlideshowWidget($galleryID, $width, $height)
     *
     * Function for templates without widget support
     * @param integer $galleryID
     * @param string $width
     * @param string $height
     * @return echo the widget content
     */
    function nggSlideshowWidget($galleryID, $width = '', $height = '')
    {
        $widget = new C_Widget_Slideshow();
        echo $widget->render_slideshow($galleryID, $width, $height);
    }

    /**
     * nggDisplayRandomImages($number, $width, $height, $exclude, $list,$show)
     *
     * Function for templates without widget support
     * @return echo the widget content
     */
    function nggDisplayRandomImages($number, $width = '75', $height = '50', $exclude = 'all', $list = '', $show = 'thumbnail')
    {
        $options = array(
            'title'    => false,
            'items'    => $number,
            'show'     => $show ,
            'type'     => 'random',
            'width'    => $width,
            'height'   => $height,
            'exclude'  => $exclude,
            'list'     => $list,
            'webslice' => false
        );
        $widget = new C_Widget_Gallery();
        $widget->widget($args = array('widget_id' => 'sidebar_1'), $options);
    }

    /**
     * nggDisplayRecentImages($number, $width, $height, $exclude, $list, $show)
     *
     * Function for templates without widget support
     * @return echo the widget content
     */
    function nggDisplayRecentImages($number, $width = '75', $height = '50', $exclude = 'all', $list = '', $show = 'thumbnail')
    {
        $options = array(
            'title'    => false,
            'items'    => $number,
            'show'     => $show ,
            'type'     => 'recent',
            'width'    => $width,
            'height'   => $height,
            'exclude'  => $exclude,
            'list'     => $list,
            'webslice' => false
        );
        $widget = new C_Widget_Gallery();
        $widget->widget($args = array('widget_id' => 'sidebar_1'), $options);
    }
}
