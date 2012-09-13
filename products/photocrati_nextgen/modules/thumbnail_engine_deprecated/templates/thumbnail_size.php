<div class="donotbreak">
    <input
        type="text"
        size="4"
        id="<?php echo $id ?>"
        max-length="4"
        name="settings[thumbnail_width]" 
        value="<?php echo_h($config->thumbnail_width) ?>"
    /><label for='<?php echo $id; ?>'>w</label>

    <input
        type="text" 
        size="4"
        id='<?php echo $id; ?>_height'
        max-length="4"
        name="settings[thumbnail_height]" 
        value="<?php echo_h($config->thumbnail_height)?>"
    /><label for='<?php echo $id; ?>_height'>h</label>
</div>
