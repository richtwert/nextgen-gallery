jQuery(function($) {
    $('input[name="photocrati-nextgen_basic_slideshow[show_alternative_view_link]"]')
        .nextgen_radio_toggle_tr('1', $('#tr_photocrati-nextgen_basic_slideshow_alternative_view'))
        .nextgen_radio_toggle_tr('1', $('#tr_photocrati-nextgen_basic_slideshow_alt_view_link_text'));

    $('input[name="photocrati-nextgen_basic_slideshow[show_return_link]"]')
        .nextgen_radio_toggle_tr('1', $('#tr_photocrati-nextgen_basic_slideshow_return_link_text'));
});