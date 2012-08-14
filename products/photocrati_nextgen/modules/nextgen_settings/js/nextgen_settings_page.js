jQuery(function($){
	// Activate accordions
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });

	// When the lightbox library is changed, display it's properties
	$('#lightbox_library').change(function(){
		var selected = $(this).find(':selected');
		$('#lightbox_library_code').val(selected.attr('code'));
		$('#lightbox_library_stylesheets').val(selected.attr('css_stylesheets'));
		$('#lightbox_library_scripts').val(selected.attr('scripts'));
	}).change();

	// Toggle the advanced settings for lightbox libraries
	$('#lightbox_library_advanced_toggle').click(function(){
		e.preventDefault();
		$("#lightbox_library_advanced_settings").toggle(500, 'swing', function(){
			var btn = $('#lightbox_library_advanced_toggle');
			if ($(this).hasClass('hidden')) {
				$(this).removeClass('hidden');
				btn.text(btn.attr('active_label'));
			}
			else {
				$(this).addClass('hidden');
				btn.text(btn.attr('hidden_label'));
			}
		});
	});
});