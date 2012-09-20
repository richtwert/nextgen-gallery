<?php

// XXX move this inclusion elsewhere?
$registry = C_Component_Registry::get_instance();
$path = $registry->get_module_dir('photocrati-nextgen-legacy');

require_once ($path . '/lib/swfobject.php');

$anchor = 'ngg-slideshow-' . $displayed_gallery_id . '-' . $current_page;
$aspect_ratio = $gallery_width / $gallery_height;
$width = $gallery_width;
$height = $gallery_height;

		if ($cycle_interval == 0)
			$cycle_interval = 1;

    // init the flash output
    $swfobject = new swfobject( $flash_path, 'so' . $displayed_gallery_id, $width, $height, '7.0.0', 'false');

    $swfobject->message = '<p>'. __('The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed.', 'nggallery').'</p>';
    $swfobject->add_params('wmode', 'opaque');
    $swfobject->add_params('allowfullscreen', 'true');
    $swfobject->add_params('bgcolor', $flash_screen_color, 'FFFFFF', 'string', '#');
    $swfobject->add_attributes('styleclass', 'slideshow');
    $swfobject->add_attributes('name', 'so' . $displayed_gallery_id);

    // adding the flash parameter
    //$swfobject->add_flashvars( 'file', urlencode ( trailingslashit ( home_url() ) . 'index.php?callback=imagerotator&gid=' . $displayed_gallery_id ) );
    $swfobject->add_flashvars( 'file', urlencode ( $mediarss_link ) );
    $swfobject->add_flashvars( 'shuffle', $flash_shuffle, 'true', 'bool');
    // option has oposite meaning : true should switch to next image
    $swfobject->add_flashvars( 'linkfromdisplay', !$flash_next_on_click, 'false', 'bool');
    $swfobject->add_flashvars( 'shownavigation', $flash_navigation_bar, 'true', 'bool');
    $swfobject->add_flashvars( 'showicons', $flash_loading_icon, 'true', 'bool');
    $swfobject->add_flashvars( 'kenburns', $flash_slow_zoom, 'false', 'bool');
    $swfobject->add_flashvars( 'overstretch', $flash_stretch_image, 'false', 'string');
    $swfobject->add_flashvars( 'rotatetime', $cycle_interval, 5, 'int');
    $swfobject->add_flashvars( 'transition', $flash_transition_effect, 'random', 'string');
    $swfobject->add_flashvars( 'backcolor', $flash_background_color, 'FFFFFF', 'string', '0x');
    $swfobject->add_flashvars( 'frontcolor', $flash_text_color, '000000', 'string', '0x');
    $swfobject->add_flashvars( 'lightcolor', $flash_rollover_color, '000000', 'string', '0x');
    $swfobject->add_flashvars( 'screencolor', $flash_screen_color, '000000', 'string', '0x');
    if ($flash_watermark_logo) {
		$ngg_options = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$swfobject->add_flashvars( 'logo', $ngg_options['wmPath'], '', 'string');
	}


    $swfobject->add_flashvars( 'audio', $flash_background_music, '', 'string');
    $swfobject->add_flashvars( 'width', $width, '260');
    $swfobject->add_flashvars( 'height', $height, '320');
    // create the output
    $out  = '<div class="slideshow">' . $swfobject->output() . '</div>';
    // add now the script code
    $out .= "\n".'<script type="text/javascript" defer="defer">';
    // load script via jQuery afterwards
    // $out .= "\n".'jQuery.getScript( "' . esc_js( includes_url('js/swfobject.js') ) . '", function() {} );';
    if ($flash_xhtml_validation) $out .= "\n".'<!--';
    if ($flash_xhtml_validation) $out .= "\n".'//<![CDATA[';
    $out .= $swfobject->javascript();
    if ($flash_xhtml_validation) $out .= "\n".'//]]>';
    if ($flash_xhtml_validation) $out .= "\n".'-->';
    $out .= "\n".'</script>';

    $out = apply_filters('ngg_show_slideshow_content', $out, $displayed_gallery_id, $width, $height);

    echo $out;

