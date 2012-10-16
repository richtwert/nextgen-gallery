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
			return _.pluck(this.selected(), 'id');
        },

		select: function(ids){
			if (!_.isArray(ids)) ids = [ids];
			this.each(function(item){
				if (ids.indexOf(item.id) >= 0) {
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

		multiple: false,

        initialize: function(){
			_.each(this.options, function(value, key){
				if (!this[key]) this[key] = value;
			}, this);
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
                if (selections.indexOf(item.id) >= 0)
                    item.set('selected', true);
                else
                    item.set('selected', false);
            });
            this.collection.trigger('selected');
        },

        render: function(){
            this.collection.each(function(item){
                var option = new this.Option({model: item});
                    this.$el.append(option.render().el);
            }, this);
			if (this.multiple) this.$el.attr('multiple', 'multiple');
			if (this.width) this.$el.width(this.width);
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
                this.$el.text(this.model.get('title'));
                this.$el.attr({
                    value:    self.model.id,
                    selected: self.model.get('selected') == true,
                });
                return this;
            }
        }),
    });


	Ngg.Views.Chosen								= Backbone.View.extend({
		tagName: 'span',

		fuzzy_search: true,
		
		placeholder: false,
		
		selection_changed: function(e, data){			
			// Select/deselect item in collection
			this.collection.each(function(item){
				if (data.selected) {
					if (item.id == parseInt(data.selected)) item.set('selected', true);
				}
				else {
					if (item.id == parseInt(data.deselected)) item.set('selected', false);
				}
			});

			// Adjust the width of the text input field
			this.trigger('width_needs_adjusting');
			
			// Trigger a change to the collection
			this.collection.trigger('selected');
		},

		initialize: function() {
			// Create the select tag. We override the selected_changed handler, as Chosen
			// does things differently.
			var self = this;
			if (this.options['placeholder']) this.placeholder = this.options.placeholder;
			this.select_tag = new Ngg.Views.SelectTag(this.options);
			this.select_tag.__proto__.selection_changed = this.selection_changed;
			this.select_tag.on('width_needs_adjusting', this.adjust_width, this);
			this.select_tag = this.select_tag.render().$el;
			if (this.placeholder) this.select_tag.attr('data-placeholder', this.placeholder);
			this.$el.empty().append(this.select_tag);
		},
		
		
		adjust_width: function(){
			var chzn_container = this.$el.find('#'+this.select_tag.attr('id')+'_chzn');
			if (!this.options.width) chzn_container.width('auto');
			var text_input = chzn_container.find('.search-field input[type=text]');
			if (this.collection.selected().length > 0)
				text_input.css('width', '25');
			else
				text_input.css('width', 'auto');
			
		},

		render: function(){
			// Chosen needs to calculate the width of the drop-down. But, before
			// it can do this, we need to append it to the DOM. To ensure that
			// the drop-down isn't visible, we'll deploy two tricks:
			// 1) Use absolute positioning, and move the element off the screen
			// 2) Make the element invisible
			this.$el.css({
				position:	'absolute',
				visibility: 'hidden',
				top:		-1000
			});
			$('body').append(this.$el);
			
			// In some browsers, the selectedIndex of a select tag is always the first element,
			// even when no particular option has explicitly been selected. We compensate for
			// that behavior.
			if (this.collection.selected().length == 0) {
				this.select_tag[0].selectedIndex = -1;
			}

			// Create the Chosen widget
			chosen_options = {};
			if (this.fuzzy_search) chosen_options.search_contains = true;
			this.select_tag.chosen(chosen_options);

			// Now that we've calculated the width, we can undo our hacks
			this.$el.detach();
			this.$el.removeAttr('style');

			// Chosen doesn't generate the width 'just right'.
			this.adjust_width();

			return this;
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
        model: Ngg.DisplayTab.Models.Source,

		selected_value: function(){
			return this.selected()[0].get('value');
		}
    });

    /**
     * Ngg.DisplayTab.Models.Gallery
     * Represents an individual gallery entity
    **/
    Ngg.DisplayTab.Models.Gallery                = Backbone.Model.extend({
		idAttribute: '<?php echo $gallery_primary_key ?>',
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

	/**
	 * Ngg.DisplayTab.Models.Display_Type
	 * Represents an individual display type
	**/
	Ngg.DisplayTab.Models.Display_Type			= Backbone.Model.extend({
		defaults: {
			title: ''
		}
	});

	/**
	 * Ngg.DisplayTab.Models.Display_Type_Collection
	 * Represents a collection of display type objects
	**/
	Ngg.DisplayTab.Models.Display_Type_Collection = Ngg.Models.SelectableItems.extend({
		model: Ngg.DisplayTab.Models.Display_Type,
		
		selected_value: function(){
			return this.selected()[0].get('name');
		}
	});
	
	/**
	 * Ngg.DisplayTab.Models.Entity
	 * Represents an entity to display on the front-end
	**/
	Ngg.DisplayTab.Models.Entity				= Backbone.Model.extend({
		entity_id: function(){
			return this.get(this.get('id_field'));
		}
	});
	
	/**
	 * Ngg.DisplayTab.Models.Entity_Collection
	 * Represents a collection of entities
	**/
	Ngg.DisplayTab.Models.Entity_Collection		= Ngg.Models.SelectableItems.extend({
		model: Ngg.DisplayTab.Models.Entity,
		
		entity_ids: function(){
			return this.map(function(item){
				return item.entity_id();
			});
		}
	});


    /*****************************************************************************
     * VIEW CLASSES
    **/

    /**
     * Ngg.DisplayTab.Views.Source_Config
     * Used to populate the source configuration tab
    **/
    Ngg.DisplayTab.Views.Source_Config             = Backbone.View.extend({
        el: '#source_configuration',

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
			var chosen = new Ngg.Views.Chosen({
				id: 'source_select',
				collection: this.sources,
				width: 150
			});
            this.$el.html('<tr><td><label>Sources:</label></td><td id="source_column"></td></tr>');
            this.$el.find('#source_column').append(chosen.render().el);
            var selected = this.sources.selected();
			if (selected.length) {
				var view_name = _.str.capitalize(selected.pop().id)+"Source";
				if (typeof(Ngg.DisplayTab.Views[view_name]) != 'undefined') {
				   var selected_view = new Ngg.DisplayTab.Views[view_name];
				   this.$el.append(selected_view.render().el);
				}
			}

            return this;
        }
    });


	Ngg.DisplayTab.Views.Display_Type_Selector = Backbone.View.extend({
		el: '#display_type_selector',

		initialize: function(){
			this.display_types = Ngg.DisplayTab.instance.display_types;
			this.render();
		},
		
		selection_changed: function(value){
			this.display_types.each(function(item){
				if (item.get('name') == value)
					item.set('selected', true);
				else
					item.set('selected', false);
			});
			$('.display_settings_form').each(function(){
				$this = $(this);
				if ($this.attr('rel') == value) $this.removeClass('hidden');
				else $this.addClass('hidden');
			});
		},

		render: function(){
			this.display_types.each(function(item){
				var display_type = new this.DisplayType;
				display_type.on('selected', function(value){
					this.selection_changed(value);
				}, this);
				display_type.model = item;
				this.$el.append(display_type.render().el);
			}, this);
			return this;
		},

		DisplayType: Backbone.View.extend({
			className: 'display_type_preview',
			
			render: function() {
				// Create all elements
				var image_container = $('<div/>').addClass('image_container');
				var img = $('<img/>').attr({
					src: wp_site_url+'/'+this.model.get('preview_image_relpath'),
					title: this.model.get('title'),
					alt: this.model.get('alt')
				});
				var inner_div = $('<div/>');
				var radio_button = $('<input/>').attr({
 					type: 'radio',
					value: this.model.get('name'),
					title: this.model.get('title'),
					checked: this.model.get('selected')
				});
				image_container.append(inner_div);
				image_container.append(img);
				inner_div.append(radio_button);
				inner_div.append(this.model.get('title'));
				this.$el.append(image_container);
			
				// Notify that the display type has been selected
				var self = this;
				radio_button.bind('change', function(e){
					self.trigger('selected', $(e.srcElement).val());
				});
				return this;
			}
		})
	});
	
	Ngg.DisplayTab.Views.Preview_Area = Backbone.View.extend({
		el: '#preview_area',
		
		initialize: function(){
			this.galleries	= Ngg.DisplayTab.instance.galleries;
			this.sources	= Ngg.DisplayTab.instance.sources;
			this.albums		= Ngg.DisplayTab.instance.albums;
			this.entities	= Ngg.DisplayTab.instance.entities;
			
			// Create the entity list
			this.entity_list = $('<ul/>').attr('id', 'entity_list').append('<li class="clear"/>');
			
			// When an entity is added to the collection, we'll add it to the DOM
			this.entities.on('add', this.render_entity, this);
			this.entities.on('remove', this.render_entity, this);
			this.entities.on('reset', function(){this.entity_list.empty().append('<li class="clear"/>');}, this);
			this.entities.on('change:sortorder', function(model){
				this.entities.remove(model, {silent: true});
				this.entities.add(model, {at: model.changed.sortorder, silent: true});
				console.log(this.entities.entity_ids());
			}, this);
		},
		
		render_entity: function(model){
			this.entity_list.find('.clear').before(new this.EntityElement({model: model}).render().el);
			if (this.entities.length == 1) {
				// Render header rows
				var sorting = '<div id="sorting" class="header_row"><strong>Sort By:</strong> <a href="#" rel="custom">Custom</a> | <a href="#">ID</a> | <a href="#">Name</a> | <a href="#">Date (Time)</a></div>';
				var exclusions = '<div id="excluding" class="header_row"><strong>Exclude:</strong> <a href="#">All</a> | <a href="#">None</a></div>';
				var ordering = '<div id="ordering" class="header_row"><strong>Order By:</strong> <a href="#">Ascending</a> | <a href="#">Descending</a></div>';
				this.$el.append(sorting);
				this.$el.append(ordering);
				this.$el.append(exclusions);
				this.$el.append(this.entity_list);				
				
				// Activate jQuery Sortable for the entity list
				this.entity_list.sortable({
					placeholder: 'placeholder',
					forcePlaceholderSize: true,
					containment: 'parent',
					opacity: 0.7,
					revert: true,
					start: function(e, ui){
						ui.placeholder.css({
							height: ui.item.height()
						});
						return true;
					},
					stop: function(e, ui) {
						ui.item.trigger('drop', ui.item.index());
					}
				});
				this.entity_list.disableSelection();
			}
			else if (this.entities.length > 1) {
				this.entity_list.sortable('refresh');
			}
		},
		
		remove_entity: function(model){
			this.entity_list.find('#'+model.get('id_field')+'_'+model.entity());
			this.entity_list.sortable('refresh');
			if (this.entities.length == 0) {
				this.$el.empty();
			}
			
		},
		
		render: function(){
			return this;
		},
		
		// Individual entity in the preview area
		EntityElement: Backbone.View.extend({
			tagName: 'li',
			
			events: {
				drop: 'item_dropped'
			},
			
			initialize: function(){
				if (this.options.model) this.model = this.options.model;
				this.id = this.model.get('id_field')+'_'+this.model.entity_id()
			},
			
			item_dropped: function(e, index){
				this.model.set('sortorder', index);
			},
			
			render: function(){
				var image_container = $('<div/>').addClass('image_container');
				var img = $('<img/>').attr({
					src: this.model.get('thumb_url'),
					alt: this.model.get('title'),
					width: this.model.get('thumb_size').width,
					height: this.model.get('thumb_size').height
				});
				image_container.append(img);
				this.$el.append(image_container).addClass('ui-state-default');
				
				// Add exclude checkbox
				var exclude_container = $('<div/>').addClass('exclude_container');
				exclude_container.append('Exclude?');
				var exclude_checkbox = new this.ExcludeCheckbox({model: this.model});
				exclude_container.append(exclude_checkbox.render().el);
				image_container.append(exclude_container);
				return this;
			},
			
			ExcludeCheckbox: Backbone.View.extend({
				tagName: 'input',
				
				events: {
					'change': 'entity_excluded'
				},
				
				entity_excluded: function(e){
					this.model.set('exclude', e.srcElement.checked);
				},
				
				initialize: function(){
					if (this.options.model) this.model = this.options.model;
				},
				
				render: function(){
					this.$el.attr({
						checked: this.model.get('checked'),
						type: 'checkbox'
					});
					return this;
				}
			})
		})
	});


	// Additional source configuration views. These will be rendered dynamically by PHP.
	// Adapters will add them.
	Ngg.DisplayTab.Views.GalleriesSource = Backbone.View.extend({
		tagName: 'tbody',

		initialize: function(){
			this.galleries = Ngg.DisplayTab.instance.galleries;
		},

		render: function(){
			var select = new Ngg.Views.Chosen({
				collection: this.galleries,
				multiple: true
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
 		fetch_limit: 50,

		fetch_url: photocrati_ajax_url,

		fetch_entities: function(limit, offset){
			// Create the request
			var request = {
				action: 'get_displayed_gallery_entities',
				displayed_gallery: this.displayed_gallery.toJSON(),
				limit: limit ? limit : this.fetch_limit,
				offset: offset ? offset : 0
			};

			// Request the entities from the server
			var self = this;
			$.post(this.fetch_url, request, function(response){
				if (!_.isObject(response)) response = JSON.parse(response);
				
				self.entities.reset();
				_.each(response.entities, function(item){
					self.entities.push(item);
				});
				
				// Continue fetching ?
				if (response.count >= response.limit+response.offset) {
					self.fetch_entities(response.limit, response.offset+response.limit);
				}
			});
		},

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
			this.display_types = new Ngg.DisplayTab.Models.Display_Type_Collection(
				<?php echo $display_types ?>
			);
			this.entities = new Ngg.DisplayTab.Models.Entity_Collection();
			

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

			// Bind to the 'selected' event for the display types collection, updating the displayed gallery
			this.display_types.on('selected', function(){
				this.displayed_gallery.set('display_type', this.display_types.selected_value());
			}, this);
			
			// Bind to the 'selected' event for the source, updating the displayed gallery
			this.sources.on('selected', function(){
				this.displayed_gallery.set('source', this.sources.selected_value());
			}, this);
        },

        // Updates the selected container_ids for the displayed gallery
        update_selected_containers: function(collection){
			this.displayed_gallery.set('container_ids', this[collection].selected_ids());
			this.fetch_entities();
        },

        render: function(){
			new Ngg.DisplayTab.Views.Source_Config();
			new Ngg.DisplayTab.Views.Display_Type_Selector();
			new Ngg.DisplayTab.Views.Preview_Area();
        }
    });
    Ngg.DisplayTab.instance = new Ngg.DisplayTab.App();
    Ngg.DisplayTab.instance.render();
});