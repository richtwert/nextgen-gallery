<?php $included = $gallery_image_id ? ($image->included ? TRUE : FALSE) : TRUE; ?>
<li class='<?php echo($included ? 'image': 'image hidden_img') ?>' rel="<?php echo_h($order) ?>" title="Drag to sort">
    
    <!-- Display Thumbnail -->
    <div class='thumbnail'>
        <?php echo $image->to_thumbnail_img_tag() ?>
    </div>
    
    <!-- Display Hide/Show and Edit links -->
    <div class="image_actions">
        <a href="" class="hide_or_show" rel="<?php echo_h($order)?>">
            <?php echo_h($included ? _('Hide') :_('Show') ); ?>
        </a> | 
        <a href="" class="edit" rel="<?php echo_h($order)?>">
            <?php echo_h(_('Edit')) ?>
        </a>
    </div> 
    
    <!-- Image Included Field -->
    <input
        class="image_included"
        name='images[<?php echo_h($order)?>][included]'
        value="<?php echo_h($included ? 1 : 0)?>"
        type="hidden"
        rel="<?php echo_h($order)?>"
    />
    
    <!-- Gallery Image ID -->
    <input
        name="images[<?php echo_h($order)?>][gallery_image_id]"
        value="<?php echo_h($gallery_image_id)?>"
        type="hidden"
        class="gallery_image_id"
    />
    
    <!-- Attached Gallery Image ID -->
    <input
        name="images[<?php echo_h($order)?>][attached_gallery_image_id]"
        value="<?php echo_h($attached_gallery_image_id)?>"
        type="hidden"
        class="attached_gallery_image_id"
    />
    
    <!-- Render other fields -->
    <table class="hidden image_form" rel="<?php echo_h($order) ?>">
        <td colspan='2'>
            <div class='errors'></div>
        </td>
        <?php foreach ($fields as $field) echo $field; ?>
    </table>
</li>