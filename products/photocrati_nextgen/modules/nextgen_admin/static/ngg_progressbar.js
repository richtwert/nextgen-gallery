(function($){

    $.nggProgressBar = function(options){
        var progressBar = {
            defaults: {
                starting_value: 0,
                ready: function(){}
            },

            // Initializes the progress bar
            init: function(options){

                // Set the options
                this.options = $.extend(this.defaults, options);

                // Display the sticky Gritter notification
                this.gritter_id = this.find_gritter(window).add({
                    progressBar: this,
                    sticky: true,
                    title:  this.options.title,
                    text:   "<div class='ngg_progressbar'><div></div></div>",
                    after_open: function(el){
                        var obj = $(el).data('nggProgressBar');
                        obj.ready();
                    }
                });
                this.gritter_el = $('#gritter-item-'+this.gritter_id);
                this.status_el = this.gritter_el.find('.ngg_progressbar:first div');
                this.gritter_el.data('nggProgressBar', this);

                // Set the ready callback
                this.ready = this.options.ready;

                // Set the starting value
                this.set(this.options.starting_value);
            },

            set: function(percent){
              percent = percent + "%";
              this.status_el.css("width", percent).text(percent);
            },

            // Increases the progress bar by a percentage point
            increase: function() {
              debugger;
              this.status_el.css("width");
            },

            close: function(){
                this.find_gritter(window).remove(this.gritter_id);
            },

            find_gritter: function(win){
                var retval = win;
                try {
                    while (retval.document !== retval.parent.document) retval = retval.parent;
                }
                catch (ex){
                    if (typeof(console) != "undefined") console.log(ex);
                }
                return retval.jQuery.gritter
            }
        };

        progressBar.init(options);

        return progressBar;
    };

})(jQuery);