jQuery(function($)
{
    $("input.ngg_settings_template").each(function(i, el) {
        el = $(el);
        el.autocomplete({
            source: nextgen_settings_templates_available_files,
            select: function(event, ui)
            {
                var re = new RegExp('^.+: (.+)');
                var val = re.exec(ui.item.value);
                $("#" + el.attr('id')).val(val[1]);
                return false;
            }
        });
    });
});
