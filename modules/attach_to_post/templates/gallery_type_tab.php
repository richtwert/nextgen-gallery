<div id="gallery_type_tab">
    <?php foreach ($gallery_types as $gallery_type => $preview): ?>
    <div class="gallery_type">
        
        <div class="preview_area">
            <?php echo $preview ?>
        </div>
        
        <input
            <?php checked($gallery_type, $selected_gallery_type)?>
            type="radio"
            name="gallery_type"
            class="gallery_type_selector"
            value="<?php echo_h($gallery_type) ?>"
        />
    </div>
    <?php endforeach ?>
</div>