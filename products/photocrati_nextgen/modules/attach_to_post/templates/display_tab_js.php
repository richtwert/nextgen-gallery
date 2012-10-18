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

		deselect_all: function(){
			this.each(function(item){
				item.set('selected', false);
			});
		},

        selected_ids: function(){
			return _.pluck(this.selected(), 'id');
        },

		select: function(ids){
			if (!_.isArray(ids)) ids = [ids];
			this.each(function(item){
				if (ids.indexOf(item.id) >= 0) {
					item.set('selected', true);
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
		
		value_field: 'id',
		
		text_field: 'title',

        initialize: function(){
			_.each(this.options, function(value, key){
				this[key] = value;
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
                if (selections.indexOf(item.id) >= 0 || selections.indexOf(item.id.toString()) >= 0)
                    item.set('selected', true);
                else
                    item.set('selected', false);
            });
            this.collection.trigger('selected');
        },

        render: function(){
            this.collection.each(function(item){
                var option = new this.Option({
					model: item,
					value_field: this.value_field,
					text_field: this.text_field
				});
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
				_.each(this.options, function(value, key){
					this[key] = value;
				}, this);
                this.model.on('change', this.render, this);
            },

            render: function(){
                var self = this;
                this.$el.text(this.model.get(this.text_field));
                this.$el.attr({
                    value:    this.value_field == 'id' ? this.model.id : this.model.get(this.value_field),
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
			_.each(this.options, function(value, key){
				this[key] = value;
			}, this);
			this.select_tag = new Ngg.Views.SelectTag(this.options);
			this.select_tag.on('selected', this.selection_changed);
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
			var retval = null;
			var selected = this.selected();
			if (selected.length > 0) {
				retval = selected[0].get('value');
			}
			return retval;
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
			var retval = null;
			var selected = this.selected();
			if (selected.length > 0) {
				return selected[0].get('name');
			}
			return retval;
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
		},
		
		included_ids: function(){
			return _.compact(this.map(function(item){
				if (parseInt(item.get('exclude')) == 0) return item.entity_id();
			}));
		}
	});
	
	
	Ngg.DisplayTab.Models.SortOrder				= Backbone.Model.extend({
	});
	
	Ngg.DisplayTab.Models.SortOrder_Options		= Ngg.Models.SelectableItems.extend({
		model: Ngg.DisplayTab.Models.SortOrder
	});
	Ngg.DisplayTab.Models.SortDirection			= Backbone.Model.extend({
		
	});
	Ngg.DisplayTab.Models.SortDirection_Options = Backbone.Collection.extend({
		model: Ngg.DisplayTab.Models.SortDirection
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
					name: 'display_type',
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
		
 		fetch_limit: 50,

		fetch_url: photocrati_ajax_url,		
		
		initialize: function(){
			this.entities			= Ngg.DisplayTab.instance.entities;
			this.sources			= Ngg.DisplayTab.instance.sources;
			this.displayed_gallery	= Ngg.DisplayTab.instance.displayed_gallery;
			
			// Create the entity list
			this.entity_list		= $('<ul/>').attr('id', 'entity_list').append('<li class="clear"/>');
			
			// When an entity is added/removed to the collection, we'll add/remove it on the DOM
			this.entities.on('add', this.render_entity, this);
			this.entities.on('remove', this.render_entity, this);
			
			// When the collection is reset, we add a list item to clear the float. This is important -
			// jQuery sortable() will break without the cleared element.
			this.entities.on('reset', this.entities_reset, this);
			
			// When jQuery sortable() is finished sorting, we need to adjust the order of models in the collection
			this.entities.on('change:sortorder', function(model){
				this.entities.remove(model, {silent: true});
				this.entities.add(model, {at: model.changed.sortorder, silent: true});
				this.displayed_gallery.set('entity_ids', this.entities.included_ids());
			}, this);
			
			// Reset when the source changes
			this.sources.on('selected', this.render, this);
			
			// Fetch the initial collection of entities
			this.entities_reset();
			
			this.render();
		},
		
		entities_reset: function(e){
			this.entity_list.empty().append('<li class="clear"/>');
			this.fetch_entities();
		},
		
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
				
				_.each(response.entities, function(item){
					item = new Ngg.DisplayTab.Models.Entity(item);
					self.entities.push(item);
				});
				
				// Continue fetching ?
				if (response.count >= response.limit+response.offset) {
					self.fetch_entities(response.limit, response.offset+response.limit);
				}
			});
		},
		
		render_entity: function(model){
			this.entity_list.find('.clear').before(new this.EntityElement({model: model}).render().el);
			if (this.el.children.length == 0) {
				this.render();
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
			this.$el.empty();
			if (this.entities.length > 0) {
				// Render header rows
				this.$el.append(new this.SortButtons({
					entities: this.entities,
					displayed_gallery: this.displayed_gallery,
					sources: this.sources
				}).render().el);
				this.$el.append(new this.ExcludeButtons({
					entities: this.entities
				}).render().el);

				this.$el.append(this.entity_list);
				
				// Activate jQuery Sortable for the entity list
				this.entity_list.sortable({
					placeholder: 'placeholder',
					forcePlaceholderSize: true,
					containment: 'parent',
					opacity: 0.7,
					revert: true,
					dropOnEmpty: true,
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
			else {
				this.$el.empty();
			}
			return this;
		},
		
		ExcludeButtons: Backbone.View.extend({
			className: 'header_row',
			
			initialize: function(){
				_.each(this.options, function(value, key){
					this[key] = value;
				}, this);
			},
			
			render: function(){
				this.$el.empty();
				this.$el.append('<strong>Exclude:</strong>');
				var all_button = new this.Button({
					value: 1,
					text: 'All',
					entities: this.entities
				});
				this.$el.append(all_button.render().el);
				this.$el.append('<span class="separator">|</span>');
				var none_button = new this.Button({
					value: 0,
					text: 'None',
					entities: this.entities
				});
				this.$el.append(none_button.render().el);
				return this;
			},
			
			Button: Backbone.View.extend({
				tagName: 'a',
				
				value: 1,
				
				text: '',
				
				events: {
					click: 'clicked'
				},
				
				initialize: function(){
					_.each(this.options, function(value, key){
						this[key] = value;
					}, this);
				},
				
				clicked: function(e){
					e.preventDefault();
					this.entities.each(function(item){
						item.set('exclude', this.value);
					}, this);
				},
				
				render: function(){
					this.$el.text(this.text).attr('href', '#');
					return this;
				}
			})
		}),
		
		SortButtons: Backbone.View.extend({
			className: 'header_row',
			
			initialize: 		function(){
				_.each(this.options, function(value, key){
					this[key] = value;
				}, this);
				this.sortorder_options = new Ngg.DisplayTab.Models.SortOrder_Options();
				this.sortorder_options.on('change:selected', this.sortoption_changed, this);
				
				// Create sort directions and listen for selection changes
				this.sortdirection_options = new Ngg.DisplayTab.Models.SortDirection_Options([
					{
						value: 'ASC',
						title: 'Ascending',
						selected: this.displayed_gallery.get('order_direction') == 'ASC'
					},
					{
						value: 'DESC',
						title: 'Descending',
						selected: this.displayed_gallery.get('order_direction') == 'DESC'
					}
				]);
				this.sortdirection_options.on('change:selected', this.sortdirection_changed, this);
			},
			
			populate_sorting_fields: function(){
				// We display difference sorting buttons depending on what type of entities we're dealing with.
				var entity_types = this.sources.selected().pop().get('returns');
				if (entity_types.indexOf('images') !== -1) {
					this.fill_image_sortorder_options();
				}
				else {
					this.fill_gallery_sortorder_options();
				}
			},
			
			create_sortorder_option: function(name, title){
				return new Ngg.DisplayTab.Models.SortOrder({
					name: name,
					title: title,
					value: name,
					selected: this.displayed_gallery.get('order_by') == name
				});
			},
			
			fill_image_sortorder_options: function(){
				this.sortorder_options.reset();
				this.sortorder_options.push(this.create_sortorder_option('sortorder', 'Custom'));
				this.sortorder_options.push(this.create_sortorder_option(Ngg.DisplayTab.instance.image_key, 'Image ID'));
				this.sortorder_options.push(this.create_sortorder_option('filename', 'Filename'));
				this.sortorder_options.push(this.create_sortorder_option('alttext', 'Alt/Title Text'));
				this.sortorder_options.push(this.create_sortorder_option('imagedate', 'Date/Time'));
			},
			
			fill_gallery_sortorder_options: function(){
				this.sortorder_options.reset();
				this.sortorder_options.push(this.create_sortorder_option('' ,'Custom'));			
			},
			
			sortoption_changed: function(model){
				this.sortorder_options.each(function(item){
					item.set('selected', model.get('value') == item.get('value') ? true : false, {silent: true});
				});
				this.displayed_gallery.set('order_by', model.get('value'));
				this.entities.reset();
				this.$el.find('a.sortorder').each(function(){
					var $item = $(this);
					if ($item.attr('value') == model.get('value'))
						$item.addClass('selected');
					else
						$item.removeClass('selected');
				});
			},
			
			sortdirection_changed: function(model){
				this.sortdirection_options.each(function(item){
					item.set('selected', model.get('value') == item.get('value') ? true : false, {silent: true});
				});
				this.displayed_gallery.set('order_direction', model.get('value'));
				this.entities.reset();
				this.$el.find('a.sortdirection').each(function(){
					var $item = $(this);
					if ($item.attr('value') == model.get('value'))
						$item.addClass('selected');
					else
						$item.removeClass('selected');
				});
			},
			
			render: function(){
				this.$el.empty();
				this.populate_sorting_fields();
				this.$el.append('<strong>Sort By:</strong>');
				this.sortorder_options.each(function(item, index){
					var button = new this.Button({model: item, className: 'sortorder'});
					this.$el.append(button.render().el);
					if (this.sortorder_options.length-1 > index) {
						this.$el.append('<span class="separator">|</span>');
					}
				}, this);
				this.$el.append('<strong style="margin-left: 30px;">Order By:</strong>');
				this.sortdirection_options.each(function(item, index){
					var button = new this.Button({model: item, className: 'sortdirection'});
					this.$el.append(button.render().el);
					if (this.sortdirection_options.length-1 > index) {
						this.$el.append('<span class="separator">|</span>');						
					}
				}, this);
				return this;
			},
			
			Button: Backbone.View.extend({
				tagName: 'a',
				
				initialize: function(){
					_.each(this.options, function(value, key){
						this[key] = value;
					}, this);
				},
				
				events: {
					click: 'clicked'
				},
				
				clicked: function(e){
					e.preventDefault();
					this.model.set('selected', true);
				},
				
				render: function(){
					this.$el.attr({
						value: this.model.get('value'),
						href: '#'
					});
					this.$el.text(this.model.get('title'));
					if (this.model.get('selected')) this.$el.addClass('selected');
					return this;
				}
			})
		}),
		
		// Individual entity in the preview area
		EntityElement: Backbone.View.extend({
			tagName: 'li',
			
			events: {
				drop: 'item_dropped'
			},
			
			initialize: function(){
				_.each(this.options, function(value, key){
					this[key] = value;
				}, this);
				this.id = this.model.get('id_field')+'_'+this.model.entity_id()
			},
			
			item_dropped: function(e, index){
				this.model.set('sortorder', index);
			},
			
			render: function(){
				var image_container = $('<div/>').addClass('image_container');
				var img = $('<img/>').attr({
					rel: this.model.id,
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
				
				type_set: false,
				
				entity_excluded: function(e){
					this.model.set('exclude', e.srcElement.checked);
				},
				
				initialize: function(){
					_.each(this.options, function(value, key){
						this[key] = value;
					}, this);
					this.model.on('change:exclude', this.render, this);
				},
				
				render: function(){
					if (!this.type_set) {
						this.$el.attr('type', 'checkbox');
						this.type_set = true;
					}
					this.$el.attr('checked', parseInt(this.model.get('exclude')) == 1);
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
	
	Ngg.DisplayTab.Views.AlbumsSource = Backbone.View.extend({
		tagName: 'tbody',
		
		initialize: function(){
			this.albums 	= Ngg.DisplayTab.instance.albums;
		},
		
		render: function(){
			var album_select = new Ngg.Views.Chosen({
				collection: this.albums,
				multiple: true,
				text_field: 'name'
			});
			this.$el.empty();
			this.$el.append('<tr><td><label>Albums</label></td><td class="albums_column"></td></tr>');
			this.$el.find('.albums_column').append(album_select.render().el);
			return this;
		}
	});
	
	
	Ngg.DisplayTab.Views.SaveButton = Backbone.View.extend({
		el: '#save_displayed_gallery',
		
		errors_el: '#errors',
		
		displayed_gallery: null,
		
		events: {
			click: 'clicked'
		},
		
		initialize: function(){
			this.displayed_gallery	= Ngg.DisplayTab.instance.displayed_gallery;
			this.entities			= Ngg.DisplayTab.instance.entities;
			this.render();
		},
		
		clicked: function(){
			this.set_display_settings();
			var request = {
				action: 'save_displayed_gallery',
				displayed_gallery: this.displayed_gallery.toJSON()
			};
			
			var self = this;
			$.post(photocrati_ajax_url, request, function(response){
				if (!_.isObject(response)) response = JSON.parse(response);
				if (response['validation_errors'] != undefined) {
					$(self.errors_el).empty().append(response.validation_errors);
				}
				else if (response['error'] != undefined) {
					alert(response.error);
				}
				else {
					var id_field = response.displayed_gallery.id_field;
					var id = response.displayed_gallery[id_field];
					self.displayed_gallery.set('id', id);
					var editor = parent.tinyMCE.activeEditor;
					var preview_url = ngg_displayed_gallery_preview_url + '?id='+id;
					var snippet = "<img class='ngg_displayed_gallery' src='"+preview_url+"'/>";
					if (editor.getContent().indexOf(preview_url) < 0)
						editor.execCommand('mceInsertContent', false, snippet);
					close_attach_to_post_window();
				}
			});
		},
		
		set_display_settings: function(){
			var display_type = this.displayed_gallery.get('display_type');
			if (display_type) {
				// Collect display settings
				var form = $("form[rel='"+display_type+"']");
				var display_settings	= (function(item){
					var obj = {};
					item.serializeArray().forEach(function(item){
						var parts = item.name.split('[');
						var current_obj = obj;
						for (var i=0; i<parts.length; i++) {
							var part = parts[i].replace(/\]$/, '');
							if (!current_obj[part]) {
								if (i == parts.length-1)
									current_obj[part] = item.value;
								else
									current_obj[part] = {};
							}
							current_obj = current_obj[part];
						}
					});
					return obj;
				})(form);
				
				// Set display settings for displayed gallery
				this.displayed_gallery.set('display_settings', display_settings[display_type]);
			}
		},
		
		render: function(){
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
			this.display_types = new Ngg.DisplayTab.Models.Display_Type_Collection(
				<?php echo $display_types ?>
			);
			this.entities = new Ngg.DisplayTab.Models.Entity_Collection();

			// Pre-select current displayed gallery values
			if (this.displayed_gallery.get('source')) {
				
				// Pre-select containers
				if (this.displayed_gallery.get('container_ids')) {
					_.each(this.displayed_gallery.get('container_ids'), function(id){
						var container = this[this.displayed_gallery.get('source')].find(function(item){
							return item.id == id;
						});
						if (container) container.set('selected', true);
					}, this);
				}
				
				// Pre-select display type
				if (this.displayed_gallery.get('display_type')) {
					var display_type = this.display_types.find(function(item){
						return item.get('name') == this.displayed_gallery.get('display_type');
					}, this);
					if (display_type) display_type.set('selected', true);
					
				}
			}

            // Bind to the 'selected' event for each of the collections, and update the displayed
            // gallery object's 'container_ids' attribute when something has changed
            collections = ['galleries', 'albums', 'tags'];
            _.each(collections, function(collection){
                this[collection].on('selected', function(){this.update_selected_containers(collection);}, this);
            }, this);

			// Bind to the 'selected' event for the display types collection, updating the displayed gallery
			this.display_types.on('change:selected', function(){
				this.displayed_gallery.set('display_type', this.display_types.selected_value());
			}, this);
			
			// Bind to the 'selected' event for the source, updating the displayed gallery
			this.sources.on('selected', function(){
				this.displayed_gallery.set('source', this.sources.selected_value());
				this.galleries.deselect_all();
				this.albums.deselect_all();
				this.tags.deselect_all();
				this.preview_area.render();
			}, this);
			
			// Synchronize changes made to entities with the displayed gallery
			this.entities.on('reset add remove change:exclude', function(){
				this.displayed_gallery.set('entity_ids', this.entities.included_ids());
			}, this);
        },

        // Updates the selected container_ids for the displayed gallery
        update_selected_containers: function(collection){
			this.displayed_gallery.set('container_ids', this[collection].selected_ids());
			this.entities.reset();
        },

        render: function(){
			new Ngg.DisplayTab.Views.Source_Config();
			new Ngg.DisplayTab.Views.Display_Type_Selector();
			this.preview_area = new Ngg.DisplayTab.Views.Preview_Area();
			new Ngg.DisplayTab.Views.SaveButton();
        }
    });
    Ngg.DisplayTab.instance = new Ngg.DisplayTab.App();
    Ngg.DisplayTab.instance.render();
});