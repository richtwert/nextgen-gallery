(function($){

    $.nggProgressBar = function(options){
        var progressBar = {
            defaults: {
                starting_value: 0
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
                });

                // Find the gritter element added
                this.find_gritter_el(window);

                // Set the starting value
                this.set(this.options.starting_value);
            },

            set: function(percent){
              percent = percent + "%";
              this.status_el.css("width", percent).text(percent);
            },

            // Increases the progress bar by a percentage point
            increase: function() {
              this.status_el.css("width");
            },

            // Closes the progress bar
            close: function(){
                this.find_gritter(window).remove(this.gritter_id);
            },

            // Finds the parent window
            find_parent: function(win){
                var retval = win;
                try {
                    while (retval.document !== retval.parent.document) retval = retval.parent;
                }
                catch (ex){
                    if (typeof(console) != "undefined") console.log(ex);
                }
                return retval;
            },

            // Finds the gritter library
            find_gritter: function(win){
               return this.find_parent(win).jQuery.gritter
            },


            // Finds the gritter element
            find_gritter_el: function(win){
                debugger;
                var selector = '#gritter-item-'+this.gritter_id;
                this.gritter_el = $(selector);
                if (this.gritter_el.length == 0) {
                    this.gritter_el = this.find_parent(win).jQuery(selector);
                }

                this.status_el = this.gritter_el.find('.ngg_progressbar:first div');
                this.gritter_el.data('nggProgressBar', this);

                return this.gritter_el;
            }
        };

        progressBar.init(options);

        return progressBar;
    };

})(jQuery);