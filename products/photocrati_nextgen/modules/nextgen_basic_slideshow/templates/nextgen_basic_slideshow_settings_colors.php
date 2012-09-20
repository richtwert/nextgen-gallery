<?php if ($color) { ?>
    <div id="<?php print $display_type_name . '_' . $name; ?>_colorpicker"
         class="nextgen_settings_farbtastic"
         data-nextgen-settings-farbtastic-target="<?php print $display_type_name . '_' . $name; ?>">
        <?php echo_h($value); ?>
    </div>
<?php } ?>

<label for="<?php print $display_type_name . '_' . $name; ?>"><?php print $label; ?></label>
<input type="<?php print $type; ?>"
       id="<?php print $display_type_name . '_' . $name; ?>"
       name="<?php print $display_type_name . '[' . $name . ']'; ?>"
       class="<?php print $display_type_name . '_' . $name; ?> nextgen_settings_colorpicker"
       value="<?php print (($type == 'checkbox') ? 'true' : $value); ?>"
       <?php if ($attr) { foreach ($attr as $name => $val) { ?>
           <?php print $name . "='" . $val . "'\n"; ?>
       <?php }} ?>
/>
<?php if (!is_null($text)) { ?>
    <?php print $text; ?>
<?php } ?>
