<?php
/**
* Main PHP class for the WordPress plugin NextGEN Gallery
*
* @author Alex Rabe
*
*
*/
class nggGallery {

	/**
	* Show a error messages
	*/
	function show_error($message) {
		echo '<div class="wrap"><h2></h2><div class="error" id="error"><p>' . $message . '</p></div></div>' . "\n";
	}

	/**
	* Show a system messages
	*/
	function show_message($message) {
		echo '<div class="wrap"><h2></h2><div class="updated fade" id="message"><p>' . $message . '</p></div></div>' . "\n";
	}

	/**
	* Renders a section of user display code.  The code is first checked for in the current theme display directory
	* before defaulting to the plugin
	* Call the function :	nggGallery::render ('template_name', array ('var1' => $var1, 'var2' => $var2));
	*
	* @autor John Godley
	* @param string $template_name Name of the template file (without extension)
	* @param string $vars Array of variable name=>value that is available to the display code (optional)
	* @param bool $callback In case we check we didn't find template we tested it one time more (optional)
	* @return void
	**/
	function render($template_name, $vars = array (), $callback = false) {
		foreach ($vars AS $key => $val) {
			$$key = $val;
		}

		// hook into the render feature to allow other plugins to include templates
		$custom_template = apply_filters( 'ngg_render_template', false, $template_name );

		if ( ( $custom_template != FALSE ) &&  file_exists ($custom_template) ) {
			include ( $custom_template );
		} else if (file_exists (STYLESHEETPATH . "/nggallery/$template_name.php")) {
			include (STYLESHEETPATH . "/nggallery/$template_name.php");
		} else if (file_exists (NGGALLERY_ABSPATH . "/view/$template_name.php")) {
			include (NGGALLERY_ABSPATH . "/view/$template_name.php");
		} else if ( $callback === TRUE ) {
            echo "<p>Rendering of template $template_name.php failed</p>";
		} else {
            //test without the "-template" name one time more
            $template_name = array_shift( explode('-', $template_name , 2) );
            nggGallery::render ($template_name, $vars , true);
		}
	}

	/**
	* Captures an section of user display code.
	*
	* @autor John Godley
	* @param string $template_name Name of the template file (without extension)
	* @param string $vars Array of variable name=>value that is available to the display code (optional)
	* @return void
	**/
	function capture ($template_name, $vars = array ()) {
		ob_start ();
		nggGallery::render ($template_name, $vars);
		$output = ob_get_contents ();
		ob_end_clean ();

		return $output;
	}

	/**
	 * nggGallery::graphic_library() - switch between GD and ImageMagick
	 *
	 * @return path to the selected library
	 */
	function graphic_library() {

		$ngg_options = get_option('ngg_options');

		if ( $ngg_options['graphicLibrary'] == 'im')
			return NGGALLERY_ABSPATH . '/lib/imagemagick.inc.php';
		else
			return NGGALLERY_ABSPATH . '/lib/gd.thumbnail.inc.php';

	}

	/**
	 * Look for the stylesheet in the theme folder
	 *
	 * @return string path to stylesheet
	 */
	function get_theme_css_file() {

  		// allow other plugins to include a custom stylesheet
		$stylesheet = apply_filters( 'ngg_load_stylesheet', FALSE );

		if ( $stylesheet !== FALSE )
			return ( $stylesheet );
		elseif ( file_exists (STYLESHEETPATH . '/nggallery.css') )
			return get_stylesheet_directory_uri() . '/nggallery.css';
		else
			return false;
	}

	/**
	 * Support for i18n with wpml, polyglot or qtrans
	 *
	 * @param string $in
	 * @param string $name (optional) required for wpml to determine the type of translation
	 * @return string $in localized
	 */
	function i18n($in, $name = null) {

		if ( function_exists( 'langswitch_filter_langs_with_message' ) )
			$in = langswitch_filter_langs_with_message($in);

		if ( function_exists( 'polyglot_filter' ))
			$in = polyglot_filter($in);

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ))
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($in);

        if (is_string($name) && !empty($name) && function_exists('icl_translate'))
            $in = icl_translate('plugin_ngg', $name, $in, true);

		$in = apply_filters('localization', $in);

		return $in;
	}

    /**
     * This function register strings for the use with WPML plugin (see http://wpml.org/ )
     *
     * @param object $image
     * @return void
     */
    function RegisterString($image) {
        if (function_exists('icl_register_string')) {
            global $wpdb;
            icl_register_string('plugin_ngg', 'pic_' . $image->pid . '_description', $image->description, TRUE);
            icl_register_string('plugin_ngg', 'pic_' . $image->pid . '_alttext', $image->alttext, TRUE);
        }
    }

	/**
	 * Slightly modfifed version of pathinfo(), clean up filename & rename jpeg to jpg
	 *
	 * @param string $name The name being checked.
	 * @return array containing information about file
	 */
	function fileinfo( $name ) {

		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);

		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );

		if ( empty($filepart) )
			return false;

		// required until PHP 5.2.0
		if ( empty($filepart['filename']) )
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );

		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );

		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];

		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];

		return $filepart;
	}


	/**
	 * Register more capabilities for custom use and add it to the administrator
	 *
	 * @since 1.5.0
	 * @param string $capability
	 * @param bool $register the new capability automatic to the admin role
	 * @return void
	 */
	function add_capabilites( $capability , $register = TRUE ) {
		global $_ngg_capabilites;

		if ( !is_array($_ngg_capabilites) )
			$_ngg_capabilites = array();

		$_ngg_capabilites[] = $capability;

		if ( $register ) {
			$role = get_role('administrator');
			if ( !empty($role) )
				$role->add_cap( $capability );
		}

	}

    /**
     * Check for mobile user agent
     *
     * @since 1.6.0
     * @author Part taken from WPtouch plugin (http://www.bravenewcode.com)
     * @return bool $result of  check
     */
    function detect_mobile_phone() {

        $useragents = array();

        // Check if WPtouch is running
        if ( function_exists('bnc_wptouch_get_user_agents') )
            $useragents = bnc_wptouch_get_user_agents();
        else {
        	$useragents = array(
                "iPhone",  			 // Apple iPhone
        		"iPod", 			 // Apple iPod touch
        		"Android", 			 // 1.5+ Android
        		"dream", 		     // Pre 1.5 Android
        		"CUPCAKE", 			 // 1.5+ Android
        		"blackberry9500",	 // Storm
        		"blackberry9530",	 // Storm
        		"blackberry9520",	 // Storm	v2
        		"blackberry9550",	 // Storm v2
        		"blackberry9800",	 // Torch
        		"webOS",			 // Palm Pre Experimental
        		"incognito", 		 // Other iPhone browser
        		"webmate" 			 // Other iPhone browser
        	);

        	asort( $useragents );
         }

        // Godfather Steve says no to flash
        if ( is_array($useragents) )
            $useragents[] = "iPad";  // Apple iPad;

        // WPtouch User Agent Filter
        $useragents = apply_filters( 'wptouch_user_agents', $useragents );

 		foreach ( $useragents as $useragent ) {
			if ( preg_match( "#$useragent#i", $_SERVER['HTTP_USER_AGENT'] ) )
				return true;
		}

        return false;
    }
}
?>
