<div class="donotbreak">
    <input
        id="<?php echo_h($id) ?>"
        type="radio"
        name="settings[<?php echo_h($name)?>]"
        value="1"
        class="<?php echo_h($name)?> yes"
        <?php checked($config->$name, 1) ?>
    />
    <?php echo_h(_('Yes')) ?>
    
    <input
        type="radio"
        name="settings[<?php echo_h($name)?>]"
        value="0"
        class="<?php echo_h($name)?> no"
        <?php checked($config->$name, 0) ?>
    />
    <?php echo_h(_('No')) ?>
</div>