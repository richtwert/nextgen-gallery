(function($) {
    $('.nextgen_displayed_gallery').css('opacity', 0.0);
    $(document).on('lazy_resources_loaded', function() {
        $('.nextgen_displayed_gallery').css('opacity', 1.0);
    });
})(jQuery);