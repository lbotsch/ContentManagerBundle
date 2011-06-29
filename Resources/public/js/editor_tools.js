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
            
            this.addTool({
                name: 'navigation',
                title: 'Navigation',
                click: function(ev) {
                    if (typeof self.navigationTool == "undefined") {
                        console.log("Initializing Navigation tool...");
                        self.navigationTool = new self.NavigationTool();
                    }
                    self.navigationTool.toggle();
                    return false;
                },
            });
            
        };
        
        this.Toolbar.addTool = function(config) {
            if (typeof config.name == "undefined") {
                throw {msg: 'config must contain a name attribute!', config: config};
            }
            
            var id = 'lubo_cm_etb_tool_'+config.name.toLowerCase().replace(' ', '_');
            config = $.extend(true, {
                id: id,
                title: config.name,
                click: function() {return false;},
                position: 'last'
            }, config);
            
            // Create tool button
            var button = null;
            var tools = $('#lubo_cm_etb_toolbar_tools');
            if (config.position == 'first') {
                tools.prepend('<span>');
                button = tools.children(':first');
            } else {
                tools.append('<span>');
                button = tools.children(':last');
            }
            button.attr({
                id: config.id,
                title: config.title,
                class: 'lubo-cm-etb-toolbar-item',
            }).append('<a>');
            button.children(':last').attr({
                href: '#',
            }).text(config.title).click(config.click);
            $.merge(this.tools, [config]);
        };
            
            /******** TOOLS ********/
            
        this.Toolbar.NavigationTool = function() {
            
            $("body").append("<div>");
            this.container = $("body").children(":last");
            this.container.attr({
                id: 'lubo_cm_etb_tool_navigation_container',
                title: 'Navigation'
            });
            
            $.jstree._themes = "/bundles/lubocontentmanager/css/jstree/";
            $('#lubo_cm_etb_tool_navigation_container').jstree({
                "plugins" : [ 
                    "themes","json_data","ui","crrm","dnd","contextmenu", "types"
                ],
                "json_data" : { 
                    "ajax" : {
                        "url" : "/app_edit.php/_etb/pagetree/get_children",
                        "data" : function(n) {
                            return {
                                "id": n.attr ? n.attr("id").replace("treenode_","") : -1,
                            };
                        },
                    }
                },
                "themes": {
                    "theme": "default",
                },
                "contextmenu": {
                    "items": function(node) {
                        var items = {
                            "create_node": {
                                "label": "Create Node",
                                "action": function() {
                                    this.create(node, "last", {
                                        "attr": {
                                            "rel": "node"
                                        },
                                        "data": "New node"
                                    });
                                }
                            },
                            "create_page": {
                                "label": "Create Page",
                                "action": function() {
                                    this.create(node, "last", {
                                        "attr": {
                                            "rel": "page"
                                        },
                                        "data": "New page"
                                    });
                                },
                                "separator_after": true,
                            },
                            "rename": {
                                "label": "Rename",
                                "action": function() {
                                    this.rename(node);
                                }
                            },
                            "delete": {
                                "label": "Delete",
                                "action": function() {
                                    this.remove(node);
                                }
                            },
                        };
                        if ($(node).attr("rel") == "page") {
                            items = $.extend({}, {
                                "visit": {
                                    "label": "Visit Page",
                                    "action": function() {
                                        window.location = $(node).attr("data-url");
                                    }
                                },
                                "set_default": {
                                    "label": "Set Default",
                                    "action": function() {
                                        var self = this;
                                        $.ajax({
                                            async : false,
                                            type: 'POST',
                                            url: "/app_edit.php/_etb/pagetree/set_default",
                                            data : {
                                                "id" : $(node).attr("id").replace("treenode_","")
                                            }, 
                                            success : function (r) {
                                                if(r.status) {
                                                    self.refresh();
                                                } else {
                                                    $('<div>The page could not be set as the default page! Do you have permission?</div>').dialog({
                                                        modal: true,
                                                        title: 'Error!',
                                                        buttons: {
                                                            Ok: function() {
                                                                $(this).dialog("close");
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    },
                                    "separator_after": true,
                                }
                            }, items);
                        }
                        return items;
                    },
                },
                "types": {
                    "valid_children" : [ "node", "page" ],
                    "types": {
                        "node": {
                            "icon": { "image": "/bundles/lubocontentmanager/img/icon_folder.png" },
                            "valid_children" : [ "node", "page" ],
                        },
                        "page": {
                            "icon": { "image": "/bundles/lubocontentmanager/img/icon_page.png" },
                            "valid_children" : [ "node", "page" ],
                        }
                    }
                },
            }).bind("create.jstree", function (e, data) {
                $.post(
                    "/app_edit.php/_etb/pagetree/create", 
                    {
                        "parent": data.rslt.parent.attr("id").replace("treenode_",""),
                        "position" : data.rslt.position,
                        "title" : data.rslt.name,
                        "type": $(data.rslt.obj).attr("rel"),
                    }, 
                    function (r) {
                        if(r.status) {
                            $(data.rslt.obj).attr("id", "treenode_" + r.id);
                            $(data.rslt.obj).attr("data-url", r.url);
                        }
                        else {
                            $.jstree.rollback(data.rlbk);
                            $('<div>The page could not be created! Do you have permission?</div>').dialog({
                                modal: true,
                                title: 'Error!',
                                buttons: {
                                    Ok: function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    }
                );
            }).bind("remove.jstree", function (e, data) {
                data.rslt.obj.each(function () {
                    $.ajax({
                        async : false,
                        type: 'POST',
                        url: "/app_edit.php/_etb/pagetree/remove",
                        data : {
                            "id" : this.id.replace("treenode_","")
                        }, 
                        success : function (r) {
                            if(!r.status) {
                                data.inst.refresh();
                                $('<div>The page could not be deleted! Do you have permission?</div>').dialog({
                                    modal: true,
                                    title: 'Error!',
                                    buttons: {
                                        Ok: function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        }
                    });
                });
            })
            .bind("rename.jstree", function (e, data) {
                $.post(
                    "/app_edit.php/_etb/pagetree/rename", 
                    {
                        "id" : $(data.rslt.obj).attr("id").replace("treenode_",""),
                        "title" : data.rslt.new_name
                    }, 
                    function (r) {
                        if(!r.status) {
                            $.jstree.rollback(data.rlbk);
                            $('<div>The page could not be renamed! Do you have permission?</div>').dialog({
                                modal: true,
                                title: 'Error!',
                                buttons: {
                                    Ok: function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        } else {
                            $(data.rslt.obj).attr("data-url", r.url);
                            $(data.rslt.obj).attr("title", r.title);
                        }
                    }
                );
            })
            .bind("move_node.jstree", function (e, data) {
                data.rslt.o.each(function (i) {
                    $.ajax({
                        async : false,
                        type: 'POST',
                        url: "/app_edit.php/_etb/pagetree/move",
                        data : {
                            "id" : $(this).attr("id").replace("treenode_",""), 
                            "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("treenode_",""), 
                            "position" : data.rslt.cp + i,
                            "title" : data.rslt.name,
                            "copy" : data.rslt.cy ? 1 : 0
                        },
                        success : function (r) {
                            if(!r.status) {
                                $.jstree.rollback(data.rlbk);
                                $('<div>The page could not be moved! Do you have permission?</div>').dialog({
                                    modal: true,
                                    title: 'Error!',
                                    buttons: {
                                        Ok: function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                            else {
                                $(data.rslt.oc).attr("id", "treenode_" + r.id);
                                if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                                    data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                                }
                            }
                        }
                    });
                });
            });
            
            $('#lubo_cm_etb_tool_navigation_container').dialog({
                autoOpen: false,
                buttons: {
                    'Reload': function() {
                        $(this).jstree("refresh");
                    },
                    'Close': function() {$(this).dialog('close');},
                },
                /*modal: true,*/
                width: 400,
            });
            
            var self = this;
            
            this.show = function() {
                self.container.dialog('open');
            };
            
            this.hide = function() {
                self.container.dialog('close');
            };
            
            this.toggle = function() {
                if (self.container.dialog('isOpen')) {
                    self.hide();
                } else {
                    self.show();
                }
            };
        };
        
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
            this.SlotTypeInterface.prototype.name = null;
            this.SlotTypeInterface.prototype.items = [];
            this.SlotTypeInterface.prototype.edit = false;
            
            
            this.SlotTypeInterface.prototype.render = function(slot) {
                var toolbar = '<div class="lubo-cm-etb-area-slot-toolbar">'
                        + '<button>Delete</button>'
                        + (this.edit ? '<button>Edit</button>' : '');
                toolbar += '</div>';
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
            
            this.init = function() {
                // Register slot types
                this.registerSlotType({
                    name: "raw_text",
                    items: [],
                    edit: function(ev) {
                        var slot = ev.data.slot;
                        var content = $('.lubo-cm-etb-area-slot-content', slot);
                        var data = content.html();
                        content.hide();
                        slot.append(
                            '<div class="lubo-cm-etb-area-slot-content-editor">'
                            + '<textarea style="width:100%;height:60px;">'+data+'</textarea><br />'
                            + '<button>Save</button> <button>Cancel</button>'
                            + '</div>');
                        var editor = $('.lubo-cm-etb-area-slot-content-editor', slot);
                        $('button:first', editor).button({
                            icons: { primary: "ui-icon-disk" }
                        }).click(function(ev) {
                            /* TODO: Save the form */
                            $.post(
                                '/app_edit.php/_etb/slot/raw_text/save',
                                {
                                    id: slot.attr('data-slot-id'),
                                    data: $('textarea', editor).attr('value')
                                },
                                function(data, status) {
                                    if (data.status) {
                                        $(editor).remove();
                                        content.html(data.html);
                                        content.show();
                                    } else {
                                        $('<div>The slot could not be saved! Do you have permission?</div>').dialog({
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
                                'json'
                            );
                        }).next().button({
                            icons: { primary: "ui-icon-cancel" }
                        }).click(function() {
                            $(editor).remove();
                            content.show();
                        });
                    }
                });
                
                this.registerSlotType({
                    name: "markdown",
                    items: [],
                    edit: function(ev) {
                        var slot = ev.data.slot;
                        var content = $('.lubo-cm-etb-area-slot-content', slot);
                        var data = content.html();
                        content.hide();
                        slot.append(
                            '<div class="lubo-cm-etb-area-slot-content-editor">'
                            + '<p>For more info about the Markdown syntax, '
                            + 'click <a href="http://daringfireball.net/projects/markdown/syntax" '
                            + 'target="_blank">here</a></p>'
                            + '<textarea style="width:100%;height:180px;">'+data+'</textarea><br />'
                            + '<button>Save</button> <button>Cancel</button>'
                            + '</div>');
                        var editor = $('.lubo-cm-etb-area-slot-content-editor', slot);
                        $('button:first', editor).button({
                            icons: { primary: "ui-icon-disk" }
                        }).click(function(ev) {
                            /* TODO: Save the form */
                            $.post(
                                '/app_edit.php/_etb/slot/markdown/save',
                                {
                                    id: slot.attr('data-slot-id'),
                                    data: $('textarea', editor).attr('value')
                                },
                                function(data, status) {
                                    if (data.status) {
                                        $(editor).remove();
                                        content.html(data.html);
                                        content.show();
                                    } else {
                                        $('<div>The slot could not be saved! Do you have permission?</div>').dialog({
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
                                'json'
                            );
                        }).next().button({
                            icons: { primary: "ui-icon-cancel" }
                        }).click(function() {
                            $(editor).remove();
                            content.show();
                        });
                    }
                });
                
                this.registerSlotType({
                    name: "menu",
                    items: [
                        function(slot) {
                            return $('<button>Configure</button>').button({
                                icons: { primary: "ui-icon-gear" }
                            }).click(function() {
                                $.get(
                                    '/app_edit.php/_etb/slot/menu/get_nodes',
                                    function(data) {
                                        if (data.status) {
                                            var panel = '<div><label>Select parent node:</label>'
                                                + '<select>';
                                            for (var i = 0, l = data.nodes.length; i < l; i++) {
                                                panel += '<option value="' + data.nodes[i].id + '">'
                                                         + data.nodes[i].title + '</option>'
                                            }
                                            panel += '</select></div>';
                                            $(panel).dialog({
                                                modal: true,
                                                title: 'Configure Menu Slot',
                                                buttons: {
                                                    Ok: function() {
                                                        var self = this;
                                                        $.post(
                                                            '/app_edit.php/_etb/slot/menu/save',
                                                            {
                                                                id: $(slot).attr('data-slot-id'),
                                                                parent: $('select:first', this).val()
                                                            },
                                                            function(data) {
                                                                if (!data.status) {
                                                                    $('<div>Could not save menu configuration!</div>').dialog({
                                                                        modal: true,
                                                                        title: 'Error!',
                                                                        buttons: {
                                                                            Ok: function() {
                                                                                $(this).dialog("close");
                                                                            }
                                                                        }
                                                                    });
                                                                } else {
                                                                    $('.lubo-cm-etb-area-slot-content', slot).html(data.html);
                                                                }
                                                                $(self).dialog("close");
                                                            },
                                                            'json'
                                                        );
                                                    },
                                                    Cancel: function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        } else {
                                            $('<div>Could not load configuration panel!</div>').dialog({
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
                                    'json'
                                );
                            });
                        },
                    ],
                });
                
                // Load slot tools
                this.load();
            };
            
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
