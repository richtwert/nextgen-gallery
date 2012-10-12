<?php

class C_Widget_Slideshow extends WP_Widget
{
    function __construct()
    {
        $widget_ops = array('classname' => 'widget_slideshow', 'description' => __('Show a NextGEN Gallery Slideshow', 'nggallery'));
        $this->WP_Widget('slideshow', __('NextGEN Slideshow', 'nggallery'), $widget_ops);
    }

    function form($instance)
    {
        global $wpdb;

        // used for rendering utilities
        $parent = C_Component_Registry::get_instance()->get_utility('I_Widget');

        // defaults
        $instance = wp_parse_args(
            (array)$instance,
            array(
                'galleryid' => '0',
                'height' => '120',
                'title' => 'Slideshow',
                'width' => '160'
            )
        );

        $parent->render_partial(
            'form_slideshow',
            array(
                'self'     => $this,
                'instance' => $instance,
                'title'    => esc_attr($instance['title']),
                'height'   => esc_attr($instance['height']),
                'width'    => esc_attr($instance['width']),
                'tables'   => $wpdb->get_results("SELECT * FROM {$wpdb->nggallery} ORDER BY 'name' ASC")
            )
        );
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['galleryid'] = (int) $new_instance['galleryid'];
        $instance['height'] = (int) $new_instance['height'];
        $instance['width'] = (int) $new_instance['width'];
        return $instance;
    }

    function widget($args, $instance)
    {
        // these are handled by extract() but I want to silence my IDE warnings that these vars don't exist
        $before_widget = NULL;
        $before_title = NULL;
        $after_widget = NULL;
        $after_title = NULL;
        $widget_id = NULL;

        extract($args);

        $title = apply_filters('widget_title', empty($instance['title']) ? __('Slideshow', 'nggallery') : $instance['title'], $instance, $this->id_base);

        $out = $this->render_slideshow($instance['galleryid'], $instance['width'], $instance['height']);

        if (!empty($out))
        {
            echo $before_widget;
            if ($title)
                echo $before_title . $title . $after_title;
            ?>
            <div class="ngg_slideshow widget">
                <?php echo $out; ?>
            </div>
            <?php
            echo $after_widget;
        }
    }

    function render_slideshow($galleryID, $irWidth = '', $irHeight = '')
    {
        require_once (dirname(__FILE__) . '/../lib/swfobject.php');

        $ngg_options = get_option('ngg_options');

        // redirect all calls to the JavaScript slideshow if wanted
        if ($ngg_options['enableIR'] !== '1' || nggGallery::detect_mobile_phone() === TRUE || NGGALLERY_IREXIST == FALSE)
            return nggShow_JS_Slideshow($galleryID, $irWidth, $irHeight, 'ngg-widget-slideshow');

        if (empty($irWidth))
            $irWidth = (int)$ngg_options['irWidth'];
        if (empty($irHeight))
            $irHeight = (int)$ngg_options['irHeight'];

        // init the flash output
        $swfobject = new swfobject($ngg_options['irURL'], 'sbsl' . $galleryID, $irWidth, $irHeight, '7.0.0', 'false');

        $swfobject->classname = 'ngg-widget-slideshow';
        $swfobject->message = __('<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the slideshow.', 'nggallery');
        $swfobject->add_params('wmode', 'opaque');
        $swfobject->add_params('bgcolor', $ngg_options['irScreencolor'], 'FFFFFF', 'string', '#');
        $swfobject->add_attributes('styleclass', 'slideshow-widget');

        // adding the flash parameter
        $swfobject->add_flashvars('file', urlencode(trailingslashit(home_url()) . 'index.php?callback=imagerotator&gid=' . $galleryID));
        $swfobject->add_flashvars('shownavigation', 'false', 'true', 'bool');
        $swfobject->add_flashvars('shuffle', $ngg_options['irShuffle'], 'true', 'bool');
        $swfobject->add_flashvars('showicons', $ngg_options['irShowicons'], 'true', 'bool');
        $swfobject->add_flashvars('overstretch', $ngg_options['irOverstretch'], 'false', 'string');
        $swfobject->add_flashvars('rotatetime', $ngg_options['irRotatetime'], 5, 'int');
        $swfobject->add_flashvars('transition', $ngg_options['irTransition'], 'random', 'string');
        $swfobject->add_flashvars('backcolor', $ngg_options['irBackcolor'], 'FFFFFF', 'string', '0x');
        $swfobject->add_flashvars('frontcolor', $ngg_options['irFrontcolor'], '000000', 'string', '0x');
        $swfobject->add_flashvars('lightcolor', $ngg_options['irLightcolor'], '000000', 'string', '0x');
        $swfobject->add_flashvars('screencolor', $ngg_options['irScreencolor'], '000000', 'string', '0x');
        $swfobject->add_flashvars('width', $irWidth, '260');
        $swfobject->add_flashvars('height', $irHeight, '320');

        // create the output
        $out  = $swfobject->output();

        // add now the script code
        $out .= "\n" . '<script type="text/javascript" defer="defer">';
        $out .= "\n" . '<!--';
        $out .= "\n" . '//<![CDATA[';
        $out .= $swfobject->javascript();
        $out .= "\n" . '//]]>';
        $out .= "\n" . '-->';
        $out .= "\n" . '</script>';

        $out = apply_filters('ngg_show_slideshow_widget_content', $out, $galleryID, $irWidth, $irHeight);

        return $out;
    }

}
