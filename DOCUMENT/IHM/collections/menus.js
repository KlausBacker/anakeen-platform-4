/*global define*/
define([
    'underscore',
    'backbone',
    'dcpDocument/models/mMenu'
], function (_, Backbone, ModelMenu)
{
    'use strict';

    return Backbone.Collection.extend({
        model: ModelMenu,

        destroy: function CollectionMenu_destroy()
        {
            var model;
            while (model = this.first()) { // jshint ignore:line
                model.destroy();
            }
        },

        _deepSearchMenu: function CollectionMenu__deepSearchMenu(contents, id, key, value)
        {
            var scope = this, subMenu, i, subSubMenu;
            if (contents) {
                for (i = 0; i < contents.length; i++) {
                    subMenu = contents[i];
                    if (subMenu.id === id) {
                        if (key) {
                            subMenu[key]=value;
                        }
                        return subMenu;
                    } else {
                        subSubMenu = scope._deepSearchMenu(subMenu.content, id, key, value);
                        if (subSubMenu) {
                            return subSubMenu;
                        }
                    }
                }
            }
            return null;
        },

        getMenu: function CollectionMenu_getMenu(id)
        {
            var menuInfo = null, menuInfoItem = null, scope = this;

            if (this.get(id)) {
                return this.get(id).attributes;
            } else {
                this.each(function (oneMenu)
                {
                    if (oneMenu.get("content")) {
                        menuInfoItem = scope._deepSearchMenu(oneMenu.get("content"), id);
                        if (menuInfoItem) {
                            menuInfo = menuInfoItem;
                        }
                    }
                });
                return menuInfo;
            }
        },


        setMenu: function CollectionMenu_setMenu(id, key, value)
        {
            var menuInfo = null, menuInfoItem = null, scope = this, menuModel=null, newContent={};

            if (this.get(id)) {
                menuModel= this.get(id);
                menuModel.set(key, value);
                return menuModel.attributes;
            } else {
                this.each(function (oneMenu)
                {
                    if (oneMenu.get("content")) {
                        menuInfoItem = scope._deepSearchMenu(oneMenu.get("content"), id, key, value);
                        if (menuInfoItem) {
                            newContent=oneMenu.get("content");
                            oneMenu.set("content", newContent);
                            menuInfo=newContent;
                            oneMenu.trigger('change');
                        }
                    }
                });
                return menuInfo;
            }
        }

    });
});