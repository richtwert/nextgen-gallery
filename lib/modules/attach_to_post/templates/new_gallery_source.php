<table id="new_gallery_fields" class="gallery_source_fields">
    
    <!-- Gallery Name -->
    <tr>
        <th>
            <label for='gallery_name'><?php echo_h(_('Gallery Name'))?>:</label>
        </th>
        <td>
            <input
                type="text"
                name="gallery_name"
                id="gallery_name"
                value="<?php echo_h($gallery_name)?>"
            />
        </td>
    </tr>
    
    <!-- Gallery Description -->
    <tr>
        <th>
            <label for='gallery_description'>
                <?php echo_h(_('Gallery Description'))?>:
            </label>
        </th>
        <td>
            <textarea name="gallery_description" id="gallery_description">
                <?php echo_h($gallery_description) ?>
            </textarea>
                
        </td>
    </tr>
</table>