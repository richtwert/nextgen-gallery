jQuery.fn.nggShowSlideshow = function(args) {
  
    var defaults = {
        id: 1,
        width: 320,
        height: 240,
        fx: 'fade',
        domain: '',
        timeout: 5000
    };
                   
    var s = jQuery.extend({}, defaults, args);
    var selector = this.selector;
	
    jQuery(selector + '-loader').empty().remove();
	
    var gallery = jQuery(selector + '-image-list');
    var self = this;

    jQuery(gallery).waitForImages(function() {
        var list = gallery.contents().detach();
        gallery.remove();

        list.appendTo(self);

        self.show();

        if (self.children().length > 1) {
            self.cycle({
                fx: s.fx,
                containerResize: 1,
                fit: 1,
                timeout: s.timeout
            });
        }
    });
};