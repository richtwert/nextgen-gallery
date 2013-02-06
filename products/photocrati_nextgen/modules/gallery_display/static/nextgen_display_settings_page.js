jQuery(function($){
	// Activate accordions
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });
    $('input, textarea').placeholder();
});

(function($) {
    $.fn.nextgen_radio_toggle_tr = function(val, target) {
        return this.each(function() {
            var $this = $(this);
            $this.bind('change', function() {
                if ($this.val() == val) {
                    target.show('slow');
                } else {
                    target.hide('slow');
                }
            });
        });

    }
})(jQuery);