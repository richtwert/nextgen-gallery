<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function nggallery_admin_style()  {

$ngg_options = get_option('ngg_options');

if (isset($_POST['css'])) {
	$act_cssfile = $_POST['css']; 
	if (isset($_POST['activate'])) {
	// save option now
	$ngg_options[activateCSS] = $_POST['activateCSS']; 
	$ngg_options[CSSfile] = $act_cssfile;
	update_option('ngg_options', $ngg_options);
	$messagetext = '<font color="green">'.__('Update successfully','nggallery').'</font>';
	}
} else {
	// get the options
	$act_cssfile = $ngg_options[CSSfile];	
}

// set the path
$real_file = NGGALLERY_ABSPATH."css/".$act_cssfile;
	
if (isset($_POST['updatecss'])) {

	if ( !current_user_can('edit_themes') )
	wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');

	$newcontent = stripslashes($_POST['newcontent']);
	if (is_writeable($real_file)) {
		$f = fopen($real_file, 'w+');
		fwrite($f, $newcontent);

		fclose($f);
		$messagetext = '<font color="green">'.__('CSS file successfully updated','nggallery').'</font>';
	}
}

// get the content of the file
if (!is_file($real_file))
	$error = 1;

if (!$error && filesize($real_file) > 0) {
	$f = fopen($real_file, 'r');
	$content = fread($f, filesize($real_file));
	$content = htmlspecialchars($content);
}

// message window
if(!empty($messagetext)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$messagetext.'</p></div>'; }
	
?>		
<div class="wrap">
	<form name="cssfiles" method="post">
	<input type="checkbox" name="activateCSS" value="1" <?php checked('1', $ngg_options[activateCSS]); ?> /> 
	<?php _e('Activate and use style sheet:','nggallery') ?>
		<select name="css" id="css" onchange="this.form.submit();">
		<?php
			$csslist = ngg_get_cssfiles();
			foreach ($csslist as $key =>$a_cssfile) {
				$css_name = $a_cssfile['Name'];
				if ($key == $act_cssfile) {
					$file_show = $key;
					$selected = " selected='selected'";
					$act_css_description = $a_cssfile['Description'];
					$act_css_author = $a_cssfile['Author'];
					$act_css_version = $a_cssfile['Version'];
				}
				else $selected = '';
				$css_name = attribute_escape($css_name);
				echo "\n\t<option value=\"$key\" $selected>$css_name</option>";
			}
		?>
		</select>
		<input type="submit" name="activate" value="<?php _e('Activate','nggallery') ?> &raquo;" class="button" />
	</form>
</div>

<div class="wrap"> 
  <?php
	if ( is_writeable($real_file) ) {
		echo '<h2>' . sprintf(__('Editing <strong>%s</strong>'), $file_show) . '</h2>';
	} else {
		echo '<h2>' . sprintf(__('Browsing <strong>%s</strong>'), $file_show) . '</h2>';
	}
	?>
	<div id="templateside">
	<ul>
	<li><strong><?php _e('Author','nggallery') ?> :</strong> <?php echo $act_css_author ?></li>
	<li><strong><?php _e('Version','nggallery') ?> :</strong> <?php echo $act_css_version ?></li>
	<li><strong><?php _e('Description','nggallery') ?> :<br /></strong> <?php echo $act_css_description ?></li>
	</ul>
	
	</div>
	<?php
	if (!$error) {
	?>
	<form name="template" id="template" method="post">
		 <div><textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1"><?php echo $content ?></textarea>
		 <input type="hidden" name="updatecss" value="updatecss" />
		 <input type="hidden" name="file" value="<?php echo $file_show ?>" />
		 </div>
<?php if ( is_writeable($real_file) ) : ?>
	<p class="submit">
<?php
	echo "<input type='submit' name='submit' value='	" . __('Update File &raquo;') . "' tabindex='2' />";
?>
</p>
<?php else : ?>
<p><em><?php _e('If this file were writable you could edit it.'); ?></em></p>
<?php endif; ?>
	</form>
	<?php
	} else {
		echo '<div class="error"><p>' . __('Oops, no such file exists! Double check the name and try again, merci.') . '</p></div>';
	}
	?>
<div class="clear"> &nbsp; </div>
</div>
	
<?php
}

/**********************************************************/
// ### Code from wordpress plugin import
// read in the css files
function ngg_get_cssfiles() {
	global $cssfiles;

	if (isset ($cssfiles)) {
		return $cssfiles;
	}

	$cssfiles = array ();
	
	// Files in wp-content/plugins/nggallery/css directory
	$plugin_root = NGGALLERY_ABSPATH."css";
	
	$plugins_dir = @ dir($plugin_root);
	if ($plugins_dir) {
		while (($file = $plugins_dir->read()) !== false) {
			if (preg_match('|^\.+$|', $file))
				continue;
			if (is_dir($plugin_root.'/'.$file)) {
				$plugins_subdir = @ dir($plugin_root.'/'.$file);
				if ($plugins_subdir) {
					while (($subfile = $plugins_subdir->read()) !== false) {
						if (preg_match('|^\.+$|', $subfile))
							continue;
						if (preg_match('|\.css$|', $subfile))
							$plugin_files[] = "$file/$subfile";
					}
				}
			} else {
				if (preg_match('|\.css$|', $file))
					$plugin_files[] = $file;
			}
		}
	}

	if ( !$plugins_dir || !$plugin_files )
		return $cssfiles;

	foreach ( $plugin_files as $plugin_file ) {
		if ( !is_readable("$plugin_root/$plugin_file"))
			continue;

		$plugin_data = ngg_get_cssfiles_data("$plugin_root/$plugin_file");

		if ( empty ($plugin_data['Name']) )
			continue;

		$cssfiles[plugin_basename($plugin_file)] = $plugin_data;
	}

	uasort($cssfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

	return $cssfiles;
}

/**********************************************************/
// parse the Header information
function ngg_get_cssfiles_data($plugin_file) {
	$plugin_data = implode('', file($plugin_file));
	preg_match("|CSS Name:(.*)|i", $plugin_data, $plugin_name);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	if (preg_match("|Version:(.*)|i", $plugin_data, $version))
		$version = trim($version[1]);
	else
		$version = '';

	$description = wptexturize(trim($description[1]));

	$name = trim($plugin_name[1]);
	$author = trim($author_name[1]);

	return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version );
}

?>