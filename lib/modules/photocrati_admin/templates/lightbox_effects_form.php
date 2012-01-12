<script type='text/javascript'>
jQuery(function($j){
    
    // Collect all lightbox libraries and append to drop-down
    $j('.lightbox_library').each(function(){
        $j('#lightbox_library').append("<option value='"+this.id+"'>"+this.id+"</option>");
    });
    
    $j('#lightbox_library').change(function(){
       var selected_id = '#'+$j(this).val();
       var fields = ['javascript_code', 'html', 'script', 'style'];
       for (var i=0; i<fields.length; i++) {
           var field = fields[i];
           $j('#'+field).val($j(selected_id +' .'+field).html());
       }
    });
    
    // Pre-select the default option
    $j('#lightbox_library').val("<?php echo $default ?>").change();
});
</script>
<?php foreach ($libraries as $library): ?>
<div class='hidden lightbox_library' id="<?php echo_h($library->name) ?>">
        <?php foreach ($library->properties as $key => $value): ?>
        <div class="<?php echo_h($key) ?>"><?php echo_h(stripslashes($value))?></div>
        <?php endforeach ?>
</div>
<?php endforeach ?>

<input type='hidden' name='settings[default]' value="1"/>
<input id='script' type='hidden' name='settings[script]'/>
<input id='style' type='hidden' name='settings[style]'/>

<table>
    <tr>
        <th>
            <label for="lightbox_library">
                <?php echo_h(_('Lightbox Library:'))?>
            </label>
        </th>
        <td>
            <select id='lightbox_library' name="settings[name]">
                <option value='none'>None</option>
            </select>
        </td>
    </tr>

    <tr>
        <th>
            <label for="javascript_code">
                <?php echo_h(_('Javascript Code:'))?>
            </label>
        </th>
        <td>
            <textarea id="javascript_code" name="settings[javascript_code]"></textarea>
        </td>
    </tr>

    <tr>
        <th>
            <label for="html">
                <?php echo_h(_('HTML Attributes:')) ?>
            </label>
        </th>
        <td>
            <textarea id="html" name="settings[html]"></textarea>
        </td>
    </tr>
</table>