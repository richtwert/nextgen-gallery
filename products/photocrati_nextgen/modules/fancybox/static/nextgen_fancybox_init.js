jQuery(function($) {
    $(".ngg-fancybox").fancybox({

		// Needed for twenty eleven
		onComplete: function(){
			$('#fancybox-wrap').css('z-index', 10000);
		}
	});
});
