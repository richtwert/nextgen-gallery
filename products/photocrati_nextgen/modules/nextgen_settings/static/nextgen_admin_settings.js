jQuery(function($){
	// Toggle the advanced settings
	$('.nextgen_advanced_toggle_link').live('click', function(e){
		e.preventDefault();
		var form_id = '#'+$(this).attr('rel');
		var btn = $(this);
		$(form_id).toggle(500, 'swing', function(){
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