<input
    type="text"
    name="settings[<?php echo_h($name)?>]"
    size="3"
    max-length="3"
    id="<?php echo_h($id) ?>"
    value="<?php echo_h($config->$name)?>"
/>