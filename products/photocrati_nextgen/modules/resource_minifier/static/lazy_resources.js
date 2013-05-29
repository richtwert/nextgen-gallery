(function($){

    window.Lazy_Resources = {
        urls: [],

        enqueue: function(url){
            this.urls.push(url);
        },

        load: function(){
            Sid.css(this.urls, function(){
                var $window = $(document);
                if (typeof($window.data('lazy_resources_loaded')) == 'undefined') {
                    $window.data('lazy_resources_loaded', true);
                    var urls = Lazy_Resources.urls;
                    $window.trigger('lazy_resources_loaded', urls);
                }
            });
        }
    };

})(jQuery);