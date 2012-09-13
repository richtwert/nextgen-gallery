<div class="donotbreak">
    <input
        id="<?php echo_h($id) ?>"
        type="radio"
        name="settings[<?php echo_h($name) ?>]"
        value="1"
        class="<?php echo_h($name) ?> yes"
        <?php checked($config->$name, 1) ?>
    />
    <label for="<?php echo_h($id); ?>"><?php echo_h(_('Yes')) ?></label>
    
    <input
        id="<?php echo_h($id) ?>_no"
        type="radio"
        name="settings[<?php echo_h($name) ?>]"
        value="0"
        class="<?php echo_h($name) ?> no"
        <?php checked($config->$name, 0) ?>
    />
    <label for='<?php echo_h($id); ?>_no'><?php echo_h(_('No')) ?></label>
</div>
