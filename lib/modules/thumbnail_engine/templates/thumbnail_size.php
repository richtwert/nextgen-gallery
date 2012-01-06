<div class="donotbreak">
    <input
        type="text"
        size="4"
        id="<?php echo $id ?>"
        max-length="4"
        name="settings[thumbnail_width]" 
        value="<?php echo_h($config->thumbnail_width) ?>"
    />w

    <input
        type="text" 
        size="4" 
        max-length="4"
        name="settings[thumbnail_height]" 
        value="<?php echo_h($config->thumbnail_height)?>"
    />h
</div>
