<div id="gallery_source_tab">
    <div class="errors"></div>
    <table>
        <tr>
            <th>
                <label for='gallery_source'><?php echo_h(_('Source'))?>:</label>
            </th>
            <td>
                <select name="gallery_source" id="gallery_source">
                    <?php foreach ($sources as $source_id => $text): ?>
                    <option <?php selected($source_id, $gallery_source)?>value="<?php echo_h($source_id)?>"><?php echo_h($text)?></option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        
    </table>
    
    <!-- Views to be displayed for each gallery source -->
    <?php foreach ($source_views as $html) echo $html; ?>
    
    
    <!-- Uploader. Should be displayed for "New Gallery" and "Existing Gallery" sources -->
    <table class="hidden" id="upload_row">
        <tr>
            <th>
                <label for="upload_images">
                    <?php echo_h(_('Upload Images'))?>:
                </label>
            </th>
            <td>
                <div id="uploader">
                    <p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
                </div>
            </td>
        </tr>
    </table>
</div>