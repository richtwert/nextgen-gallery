<input
    id="<?php echo_h($id)?>"
    type="text" 
    size="3" 
    maxlength="3" 
    name="settings[<?php echo_h($name) ?>]" 
    value="<?php echo_h($config->$name) ?>"
/>