jQuery("document").ready(function(){
	// register ajax gallery-navigation listeners
	jQuery("a.page-numbers").click(function(e) {
		return ngg_ajax_navigation(e, this);
	});
	jQuery("a.prev").click(function(e) {
		return ngg_ajax_navigation(e, this);
	});
	jQuery("a.next").click(function(e) {
		return ngg_ajax_navigation(e, this);
	});
});

function ngg_ajax_navigation_parse_url(source) {
    var match;
    var urlParameters = {};
    var search = /([^&=]+)=?([^&]*)/g;
    var decode = function (s) { return decodeURIComponent(s.replace(/\+/g, " ")); };
    while (match = search.exec(source)) {
        urlParameters[decode(match[1])] = decode(match[2]);
    }
    return urlParameters;
}

function ngg_ajax_navigation(e, obj) {
    // combine the URL in ngg_ajax.callback with the current anchors
    var old_parameters = ngg_ajax_navigation_parse_url(obj.href.split('?')[1]);
    var new_parameters = ngg_ajax_navigation_parse_url(ngg_ajax.callback.split('?')[1]);
    for (var attrname in old_parameters) {
        new_parameters[attrname] = old_parameters[attrname];
    }

    // build our new url string
    var new_url = ngg_ajax.callback.split('?')[0] + '?';
    Object.keys(new_parameters).forEach(function(key) {
        new_url = new_url + '&' + key + '=' + new_parameters[key];
    });

	// try to find gallery number by checking the parents ID until we find a matching one
	var currentNode = obj.parentNode;
	while (null != currentNode.parentNode && !jQuery.nodeName(currentNode.parentNode, "body") && "ngg-gallery-" != jQuery(currentNode.parentNode).attr("id").substring(0, 12)) {
		currentNode = currentNode.parentNode;
	}

	if (jQuery(currentNode.parentNode).attr("id")) {

        var gallery = jQuery(currentNode.parentNode);

		ngg_show_loading(e);
		
		// load gallery content
		jQuery.get(new_url, new_parameters, function (data) {
			
			// delete old content
			gallery.children().remove();
			
			// add new content
			gallery.replaceWith(data);
			
			// add ajax-navigation, again
			jQuery("document").ready(function(){
				// reset old listeners to avoid double-clicks
				jQuery("a.page-numbers").unbind("click")
                                        .click(function(e) { return ngg_ajax_navigation(e, this); });

                jQuery("a.prev").unbind("click")
                                .click(function(e) { return ngg_ajax_navigation(e, this); });

                jQuery("a.next").unbind("click")
                                .click(function(e) { return ngg_ajax_navigation(e, this); });
				
				// add shutter-listeners again
				// shutterReloaded.init('sh');
				
				ngg_remove_loading();
			});
		});
		
		// deactivate HTML link
        e.preventDefault();
	}
	
	// an error occurred, use normal link
	return true;
}

var loadingImage;

function ngg_show_loading(obj) {
	loadingImage = jQuery(document.createElement("img")).attr("src", ngg_ajax.path + "images/ajax-loader.gif").attr("alt", ngg_ajax.loading);

	jQuery("body").append(loadingImage);
	
	jQuery(loadingImage).css({
		position: "absolute",
		top: (obj.pageY + 15) + "px",
		left: (obj.pageX + 15) + "px"
	});
	
	jQuery(document).mousemove(function(e) {
		loadingImage.css({
			top: (e.pageY + 15) + "px",
			left: (e.pageX + 15) + "px"
		});
	});
}

function ngg_remove_loading() {
	jQuery(document).unbind("mousemove");
	jQuery(loadingImage).remove();
}
