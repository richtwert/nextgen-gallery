jQuery(function($){

    /*****************************************************************************
     ** NGG DEFINITION
    ***/

    /**
     Setup a namespace for NextGEN-offered Backbone components
    **/
    var Ngg = {
        Models: {},
        Views: {}
    };

    /*****************************************************************************
     ** NGG MODELS
    ***/

    /**
     * Ngg.Models.SelectableItems
     * A collection of items that can be selectable. Commonly used with the
     * Ngg.Views.SelectTag widget (view)
    **/
    Ngg.Models.SelectableItems = Backbone.Collection.extend({
        selected: function(){
            return this.filter(function(item){
                return item.get('selected') == true;
            });
        },

        selected_ids: function(){
            return selected.plunge('id');
        },

		select: function(ids){
			if (!_.isArray(ids)) ids = [ids];
			this.each(function(item){
				if (ids.indexOf(item.get('id')) >= 0) {
					item.selected = true;
				}
			});
		}
    });


    /*****************************************************************************
     ** NGG VIEWS
    ***/

    /**
     * Ngg.Views.SelectTag
     * Used to render a Select tag (drop-down list)
    **/
    Ngg.Views.SelectTag                    = Backbone.View.extend({
        tagName: 'select',

        collection: null,

        empty_text: '--Select--',

        initialize: function(){
            if (!this.collection) this.collection = this.options.collection;
        },

        events: {
            'change': 'selection_changed'
        },

        /**
         * After a selection has changed, set the 'selected' property for each item in the
         * collection
         * @triggers 'selected'
        **/
        selection_changed: function(){
            // Get selected options from DOM
            var selections = _.map(this.$el.find(':selected'), function(element){
                return $(element).attr('value');
            });

            // Set the 'selected' attribute for each item in the collection
            this.collection.each(function(item){
                if (selections.indexOf(item.get('value')) >= 0)
                    item.set('selected', true);
                else
                    item.set('selected', false);
            });
            this.collection.trigger('selected');
        },

        render: function(){
            this.$el.append(new this.Option({
                model: new Backbone.Model({
                  value: '',
                  title: this.empty_text
                })
            }).render().el);
            this.collection.each(function(item){
                var option = new this.Option({model: item});
                    this.$el.append(option.render().el);
            }, this);
            return this;
        },

        /**
         * Represents an option in the Select drop-down
        **/
        Option: Backbone.View.extend({
            tagName: 'option',

            model: null,

            initialize: function(){
                this.model.on('change', this.render, this);
            },

            render: function(){
                var self = this;
                if (!this.model) this.model = this.options.model;
                this.$el.attr({
                    value:    self.model.get('value'),
                    selected: self.model.get('selected'),
                });
                this.$el.html(this.model.get('title'));
                return this;
            }
        }),
    });


	Ngg.Views.Chosen								= Ngg.Views.SelectTag.extend({
		fuzzy_search: true,

		render: function(){
			chosen_options = {};
			if (this.fuzzy_search) chosen_options.search_contains = true;
			Ngg.Views.SelectTag.prototype.render.call(this).el.chosen(chosen_options);
		}
	});

    /*****************************************************************************
     ** DISPLAY TAB DEFINITION
    ***/

    /**
     * Setup a namespace
    **/
    Ngg.DisplayTab = {
        Models: {},
        Views: {},
        App: {}
    };

    /*****************************************************************************
     * MODEL CLASSES
    **/

    /**
     * Ngg.DisplayTab.Models.Displayed_Gallery
     * Represents the displayed gallery being edited or created by the Display Tab
    **/
    Ngg.DisplayTab.Models.Displayed_Gallery        = Backbone.Model.extend({
        defaults: {
            source: null,
            container_ids: [],
            entity_ids: [],
            display_type: null,
            display_settings: {},
            exclusions: []
        }
    });

    /**
     * Ngg.DisplayTab.Models.Source
     * Represents an individual source used to collect displayable entities from
    **/
    Ngg.DisplayTab.Models.Source                = Backbone.Model.extend({
        defaults: {
            title: '',
            selected: false
        }
    });

    /**
     * Ngg.DisplayTab.Models.Source_Collection
     * Used as a collection of all the available sources for entities
    **/
    Ngg.DisplayTab.Models.Source_Collection        = Ngg.Models.SelectableItems.extend({
        model: Ngg.DisplayTab.Models.Source
    });

    /**
     * Ngg.DisplayTab.Models.Gallery
     * Represents an individual gallery entity
    **/
    Ngg.DisplayTab.Models.Gallery                = Backbone.Model.extend({
        defaults: {
            title:     '',
            name:   ''
        }
    });

    /**
     * Ngg.DisplayTab.Models.Gallery_Collection
     * Collection of gallery objects
    **/
    Ngg.DisplayTab.Models.Gallery_Collection    = Ngg.Models.SelectableItems.extend({
        model: Ngg.DisplayTab.Models.Gallery
    });

    /**
     * Ngg.DisplayTab.Models.Album
     * Represents an individual Album object
    **/
    Ngg.DisplayTab.Models.Album                    = Backbone.Model.extend({
        defaults: {
            title: '',
            name:  ''
        }
    });

    /**
     * Ngg.DisplayTab.Models.Album_Collection
     * Used as a collection of album objects
    **/
    Ngg.DisplayTab.Models.Album_Collection        = Ngg.Models.SelectableItems.extend({
        model: Ngg.DisplayTab.Models.Album
    });

    /**
     * Ngg.DisplayTab.Models.Tag
     * Represents an individual tag object
    **/
    Ngg.DisplayTab.Models.Tag                    = Backbone.Model.extend({
        defaults: {
            title: ''
        }
    });

    /**
     * Ngg.DisplayTab.Models.Tag_Collection
     * Represents a collection of tag objects
    **/
    Ngg.DisplayTab.Models.Tag_Collection        = Ngg.Models.SelectableItems.extend({
        model: Ngg.DisplayTab.Models.Album
    });


    /*****************************************************************************
     * VIEW CLASSES
    **/

    /**
     * Ngg.DisplayTab.Views.Source_Config
     * Used to populate the source configuration tab
    **/
    Ngg.DisplayTab.Views.Source_Config             = Backbone.View.extend({
        el: $('#source_configuration'),

        selected_view: null,

        /**
         * Bind to the "sources" collection to know when a selection has been made
         * and determine what sub-view to render
        **/
        initialize: function(){
            this.sources = Ngg.DisplayTab.instance.sources;
            this.sources.on('selected', this.render, this);
            _.bindAll(this, 'render');
            this.render();
        },

        render: function(){
            var select = new Ngg.Views.SelectTag({
                collection: this.sources
            });
            this.$el.html('<tr><td><label>Sources:</label></td><td id="source_column"></td></tr>');
            this.$el.find('#source_column').append(select.render().el);

            var selected = this.sources.selected();
               if (selected.length) {
                   var view_name = _.str.capitalize(selected.pop().get('value'))+"Source";
                   if (typeof(Ngg.DisplayTab.Views[view_name]) != 'undefined') {
                      var selected_view = new Ngg.DisplayTab.Views[view_name];
                      this.$el.append(selected_view.render().el);
                   }
               }
            return this;
        }
    });

	// Additional source configuration views. These will be rendered dynamically by PHP.
	// Adapters will add them.
	Ngg.DisplayTab.Views.GalleriesSource = Backbone.View.extend({
		tagName: 'tbody',

		initialize: function(){
			this.galleries = Ngg.DisplayTab.instance.galleries;
		},

		render: function(){
			var select = new Ngg.Views.SelectTag({
				collection: this.galleries
			});
			var html = $('<tr><td><label>Galleries</label></td><td class="galleries_column"></td></tr>');
			this.$el.empty();
			this.$el.append(html);
			this.$el.find('.galleries_column').append(select.render().el);
			return this;
		}
	});

    /*****************************************************************************
     * APPLICATION
    **/
    Ngg.DisplayTab.App = Backbone.View.extend({

        /**
         * Initializes the DisplayTab object
        **/
        initialize: function(){


			// TODO: We're currently fetching ALL galleries, albums, and tags
			// in one shot. Instead, we should display the displayed_gallery's
			// containers, if there are any, otherwise get the first 25 or so.
			// We can then use AJAX to fetch the rest of batches.
            this.displayed_gallery = new Ngg.DisplayTab.Models.Displayed_Gallery(
				<?php echo $displayed_gallery ?>
			);
            this.galleries = new Ngg.DisplayTab.Models.Gallery_Collection(
				<?php echo $galleries ?>
			);
            this.albums = new Ngg.DisplayTab.Models.Album_Collection(
				<?php echo $albums ?>
			);
            this.tags = new Ngg.DisplayTab.Models.Tag_Collection(
				<?php echo $tags ?>
			);
            this.sources = new Ngg.DisplayTab.Models.Source_Collection(
				<?php echo $sources ?>
			)

			// Pre-select current displayed gallery values
			if (this.displayed_gallery.source) {
				this[this.displayed_gallery.source].select(
					this.displayed_gallery.container_ids
				);
			}

            // Bind to the 'selected' event for each of the collections, and update the displayed
            // gallery object's 'container_ids' attribute when something has changed
            collections = ['galleries', 'albums', 'tags'];
            _.each(collections, function(collection){
                this[collection].on('selected', function(){this.update_selected_containers(collection);}, this);
            }, this);
        },

        // Updates the selected container_ids for the displayed gallery
        update_selected_containers: function(collection){
            this.displayed_gallery.container_ids = this[collection].get_ids();
        },

        render: function(){
          source_config = new Ngg.DisplayTab.Views.Source_Config();
        }
    });
    Ngg.DisplayTab.instance = new Ngg.DisplayTab.App();
    Ngg.DisplayTab.instance.render();
});