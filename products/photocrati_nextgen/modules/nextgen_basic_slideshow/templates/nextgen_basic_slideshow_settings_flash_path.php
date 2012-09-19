<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>">
    <td>
        <label for="<?php print $display_type_name . '_' . $name; ?>"
               class="<?php if (!empty($text)) { echo 'tooltip'; } ?>">
            <?php print $label; ?>
            <?php if (!empty($text)) { ?>
            <span>
                    <?php print $text; ?>
                </span>
            <?php } ?>
        </label>
    </td>
    <td>
    <?php
    	if (empty($value) && !$hidden) {
    ?>
		<div class="error inline">
		<p>
			<?php _e('The path to imagerotator.swf is not defined, the slideshow will not work.','nggallery'); ?><br />
			<?php _e('If you would like to use the JW Image Rotatator, please download the player <a href="http://www.longtailvideo.com/players/jw-image-rotator/" target="_blank" >here</a> and upload it to your Upload folder (Default is wp-content/uploads).','nggallery'); ?>
		</p>
		</div>
    <?php
    	}
    ?>
        <input type="<?php print $type; ?>"
               id="<?php print $display_type_name . '_' . $name; ?>"
               name="<?php print $display_type_name . '[' . $name . ']'; ?>"
               class="<?php print $display_type_name . '_' . $name; ?>"
               value="<?php print $value; ?>"
               <?php if ($attr) { foreach ($attr as $name => $val) { ?>
                   <?php print $name . "='" . $val . "'\n"; ?>
               <?php }} ?>
        />
    
				<input type="submit" name="irDetect" class="button-secondary"  value="<?php _e('Search now','nggallery') ;?> &raquo;"/>
				<br /><span class="setting-description"><?php _e('Press the button to search automatically for the imagerotator, if you uploaded it to wp-content/uploads or a subfolder','nggallery') ?></span>
			
        <?php if (!is_null($text)) { ?>
            <?php print $text; ?>
        <?php } ?>
    </td>
</tr>
