if (typeof window.LuboCM == "undefined") {
    window.LuboCM = new function() {
        this.areas = [];
        
        this.init = function() {
            // Set page title
            document.title += ' [Edit]';
            // Initialize Toolbar
            this.Toolbar.init();
            // Init each area... 
            var self = this;
            $('.lubo-cm-etb-area-toolbar').each(function() {
                var areaTools = new self.AreaTools($(this));
                areaTools.init();
                $.merge(self.areas, [areaTools]);
            });
            this.SlotTools.init();
        };
        
        this.Toolbar = new function() {
            this.tools = [];
        };
        this.Toolbar.init = function() {
            // Initialize Tools for the Editor Toolbar
            var self = this;
        };
        
        this.Toolbar.addTool = function(config) {
            if (typeof config.name == "undefined") {
                throw {msg: 'config must contain a name attribute!', config: config};
            }
            
            var id = 'lubo_cm_etb_tool_'+config.name.toLowerCase().replace(' ', '_');
            config = $.extend(true, {
                id: id,
                title: config.name,
                class: '',
                icon: '',
                click: function() {return false;},
                position: 'last'
            }, config);
            
            // Create tool button
            var button = null;
            var tools = $('#lubo_cm_etb_toolbar_tools');
            if (config.position == 'first') {
                tools.prepend('<span class="lubo-cm-etb-toolbar-item"><button></span>');
                button = tools.children(':first').children(':first');
            } else if (config.position == 'last' || tools.children().length < config.position + 1) {
                tools.append('<span class="lubo-cm-etb-toolbar-item"><button></span>');
                button = tools.children(':last').children(':first');
            } else {
                button = $('<span class="lubo-cm-etb-toolbar-item"><button></span>');
                $(tools.children()[config.position]).before(button);
                button = button.children(':first');
            }
            button.attr({
                id: config.id,
                title: config.title,
                class: config.class,
            }).text(config.title).click(config.click).button({
                icons: { primary: config.icon }
            });
            config["button"] = button;
            
            $.merge(this.tools, [config]);
        };
            
            /******** TOOLS ********/
            
        
        
        this.AreaTools = function(area) {
            this.container = $(area).parent();
            this.area = area;
            this.areaName = $(area).attr('data-area-name');
            this.isGlobal = $(area).attr('data-area-global');
            this.pageId   = $(area).attr('data-page-id');
            this.pageType = $(area).attr('data-page-type');
            this.slotTypes = jQuery.parseJSON($(area).attr('data-allowed-slots'));
        };
            
        this.AreaTools.prototype.init = function() {
            /* Area Info */
            $(this.area).append('<span>');
            var span = $(this.area).children(':last');
            span.attr({
                class: 'lubo-cm-etb-area-info',
            });
            span.text('Area name: '+this.areaName);
            
            /* Create Tool container */
            $(this.area).append('<span>');
            span = $(this.area).children(':last');
            span.attr({
                class: 'lubo-cm-etb-area-toolbar-tools',
            });
            
            /* Create Slot tool */
            var tool = new this.CreateSlotTool(this);
            this.addTool(tool);
        };
        
        this.AreaTools.prototype.addTool = function(config) {
            if (typeof config.name == "undefined") {
                throw {msg: 'config must contain a name attribute!', config: config};
            }
            
            config = $.extend(true, {
                title: config.name,
                click: function() {return false;},
                position: 'last',
                element: '&nbsp;'
            }, config);
            
            var toolbar = $('.lubo-cm-etb-area-toolbar-tools', this.area);
            var toolContainer = null;
            if (config.position == 'first') {
                toolbar.prepend('<span>');
                toolContainer = toolbar.children(':first');
                if (config.spinner) toolbar.prepend(config.spinner);
            } else {
                if (config.spinner) toolbar.append(config.spinner);
                toolbar.append('<span>');
                toolContainer = toolbar.children(':last');
            }
            toolContainer.attr({
                class: 'lubo-cm-etb-area-toolbar-tool',
                title: config.title
            }).append(config.element);
            config.loaded();
        };
        
        this.AreaTools.prototype.CreateSlotTool = function(area) {
            this.area = area;
            var slotTypes = area.slotTypes;
            this.name = 'create_slot';
            this.spinner = $('<img class="lubo-cm-etb-area-toolbar-create-slot-spinner" '
                +'src="/bundles/lubocontentmanager/img/spinner.gif" style="display:none;" />')
            this.title = 'Create Slot';
            this.element = $('<select><option>+ Slot</option></select>');
            for (var i = 0; i < slotTypes.length; i++) {
                this.element.append('<option>');
                this.element.children(':last').attr({
                    value: slotTypes[i].id
                }).text(slotTypes[i].name);
            }
            var self = this;
            this.loaded = function() {
                self.element.selectmenu({
                    change: function(e, object){
                        if (object.index > 0) {
                            self.spinner.show();
                            $.post(
                                '/app_edit.php/_etb/slot/create',
                                {
                                    "area_name": self.area.areaName,
                                    "global":    self.area.isGlobal,
                                    "page_id":   self.area.pageId,
                                    "page_type": self.area.pageType,
                                    "slot_type": object.value,
                                },
                                function(data, status) {
                                    self.spinner.hide();
                                    if (data.status) {
                                        var notice = $('.lubo-cm-etb-area-notice', self.area.container);
                                        if (notice) notice.hide();
                                        $(self.area.container).append(data.html);
                                        var slot = $('.lubo-cm-etb-area-slot:last', self.area.container);
                                        window.LuboCM.SlotTools.loadToolbar(slot)
                                    } else {
                                        $('<div>The slot could not be created! Do you have permission?</div>').dialog({
                                            modal: true,
                                            title: 'Error!',
                                            buttons: {
                                                Ok: function() {
                                                    $(this).dialog("close");
                                                }
                                            }
                                        });
                                    }
                                },
                                "json"
                            );
                            self.element.selectmenu("index", 0);
                        }
                    }
                });
            }
        };
        
        this.SlotTools = new function() {
            this.slotTypes = {};
            
            /**
             * SlotType interface
             */
            this.SlotTypeInterface = function() {};
            this.SlotTypeInterface.prototype.slot_type = null;
            this.SlotTypeInterface.prototype.name = null;
            this.SlotTypeInterface.prototype.items = [];
            this.SlotTypeInterface.prototype.edit = false;
            
            
            this.SlotTypeInterface.prototype.render = function(slot) {
                var toolbar = '<div class="lubo-cm-etb-area-slot-toolbar">'
                        + '<button>Delete</button>'
                        + (this.edit ? '<button>Edit</button>' : '');
                toolbar += '<span class="lubo-cm-etb-area-slot-info">Slot Type: '+this.slot_type+'</span></div>';
                slot.prepend(toolbar);
                toolbar = $('div:first', slot);
                for (var i = 0,l = this.items.length; i < l; i++) {
                    toolbar.append(this.items[i].call(this, slot));
                }
                var button = $('button:first', toolbar);
                button.button({
                    icons: {
                        primary: "ui-icon-trash"
                    }
                }).click(function(ev) {
                    $('<div><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'
                            +'These items will be permanently deleted and cannot be recovered. Are you sure?</p></div>')
                        .dialog({
                            resizable: false,
                            height:140,
                            modal: true,
                            title: 'Please confirm!',
                            buttons: {
                                "Delete slot": function() {
                                    $( this ).dialog( "close" );
                                    
                                    $.post(
                                        '/app_edit.php/_etb/slot/delete',
                                        { id: slot.attr('data-slot-id') },
                                        function(data, status) {
                                            if (data.status) {
                                                $(slot).remove();
                                            } else {
                                                $('<div>The slot could not be deleted! Do you have permission?</div>').dialog({
                                                    modal: true,
                                                    title: 'Error!',
                                                    buttons: {
                                                        Ok: function() {
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            }
                                        }, 'json'
                                    );
                                },
                                Cancel: function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });
                });
                if (this.edit) {
                    button.next().button({
                        icons: {
                            primary: "ui-icon-pencil"
                        }
                    }).click({slot: slot}, this.edit);
                }
            };
            
            this.init = function() {};
            
            /**
             * Register a tool that handles a slot type
             */
            this.registerSlotType = function(slotType) {
                var t = function(){};
                t.prototype = $.extend(true, {}, this.SlotTypeInterface.prototype, slotType);
                
                if (typeof t.prototype.name == "undefined"
                        || t.prototype.name == "" || t.prototype.name == null) {
                    throw "SlotTools.registerSlotType: slotType needs to have a name!";
                }
                this.slotTypes[t.prototype.name] = new t();
            }
            
            /**
             * Find all slots and load their toolbar
             */
            this.load = function() {
                var self = this;
                $('.lubo-cm-etb-area-slot').each(function() {
                    self.loadToolbar($(this));
                });
            }
            
            /**
             * Load the SlotToolbar for a specific slot
             */
            this.loadToolbar = function(slot) {
                var slotType = $(slot).attr('data-slot-type');
                this.slotTypes[slotType].render(slot);
            }
        };
    };
}
// Init LuboCM
window.LuboCM.init();
