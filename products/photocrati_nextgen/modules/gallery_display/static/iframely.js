jQuery(function($){

	// Is this page within an iframe?
	if (window.frameElement) {
		// For CSS purposes, we identify this page as being iframely
		$('html').attr('id', 'iframely');

		// Concentrate only on the content of the page
		$('#wpwrap').html($('#wpbody').html($('#wpbody-content').html($('#ngg_page_content'))));

		// We need to ensure that any POST operation includes the "attach_to_post"
		// parameter, to display subsequent clicks in iframely.
		$('form').each(function(){
			$(this).append("<input type='hidden' name='attach_to_post' value='1'/>");
		});
		parent.resize_attach_to_post_tab(window.frameElement, true);
	}
});