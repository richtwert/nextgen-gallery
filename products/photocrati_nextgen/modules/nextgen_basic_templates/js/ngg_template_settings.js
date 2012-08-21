jQuery(function($)
{
    $("#photocrati-nextgen_basic_thumbnails_template").autocomplete({
        source: availableFiles,
        select: function(event, ui)
        {
            var re = new RegExp('^.+: (.+)');
            var val = re.exec(ui.item.value);
            $("#photocrati-nextgen_basic_thumbnails_template").val(val[1]);
            return false;
        }
    });
});
