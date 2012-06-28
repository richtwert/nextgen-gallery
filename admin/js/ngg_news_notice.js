jQuery(function($){
	$('#wp-admin-bar-ngg-menu').pointer({
		content: nggAdmin.content,
		pointerClass: 'pointer ngg_latest_news_notice',
		close: function(){
			var data = {
				action: 'hide_news_notice',
				nonce: nggAdmin.nonce
			}
			jQuery.post(ajaxurl, data);
		}
	}).pointer('open');
	!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
});