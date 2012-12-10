// Provides a function to close the TinyMCE popup window
function close_attach_to_post_window()
{
	parent.tinyMCE.activeEditor.windowManager.close(window);
}

// Adjusts the height of a frame on the page, and then executes
// the specified callback
function adjust_height_for_frame(frame, callback)
{
	var new_height		= jQuery(frame.contentDocument).height();
	var current_height	= jQuery(frame).height();
	if (current_height < new_height) jQuery(frame).height(new_height);

	if (callback != undefined)
		return callback.call(frame, new_height);
	else
		return true;
}

// Activates the attach to post screen elements
jQuery(function($){
	// Activate horizontal tabs
	$('#attach_to_post_tabs').tabs();

	// If the preview area is being displayed, emit an event for that
	$('.accordion h3').bind('click', function(e){
		if ($(this).attr('id') == 'preview_tab') {
			$('#preview_area').trigger('opened');
		}
	});

	// Activate accordion for display tab
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });

	// If the active display tab is clicked, then we assume that the user
	// wants to display the original tab content
	$('.ui-tabs-nav a').click(function(e){

		var element = e.target ? e.target : e.srcElement;

		// If the accordion tab is used to display an iframe, ensure when
		// clicked that the original iframe content is always displayed
		if ($(element).parent().hasClass('ui-state-active')) {
			var iframe = $(element.hash+' iframe');
			if (iframe.length > 0) {
				if (iframe[0].contentDocument.location != iframe.attr('src')) {
					iframe[0].contentDocument.location = iframe.attr('src');
				}
			}
		}
	});

	// Close the window when the escape key is pressed
	$(this).keydown(function(e){
		if (e.keyCode == 27) close_attach_to_post_window();
		return;
	});

	// Fade in now that all GUI elements are intact
	$('body').css({
		position: 'static',
		visibility: 'visible'
	}).animate({
		opacity: 1.0
	});
});