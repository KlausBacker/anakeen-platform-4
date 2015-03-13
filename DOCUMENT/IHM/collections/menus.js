define([
    'underscore',
    'backbone',
    'dcpDocument/models/mMenu'
], function (_, Backbone, ModelMenu)
{
    'use strict';

    return Backbone.Collection.extend({
        model: ModelMenu,

        destroy: function ()
        {
            this.invoke("trigger", "destroy");
        },

        _deepSearchMenu: function (contents, id, key, value)
        {
            var scope = this;
            var subMenu, i, subSubMenu;
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

        getMenu: function (id)
        {
            var menuInfo = null;
            var menuInfoItem = null;
            var scope = this;

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


        setMenu: function (id, key, value)
        {
            var menuInfo = null;
            var menuInfoItem = null;
            var scope = this;
            var menuModel=null;
            var currentValue={};
            var newContent={};

            if (this.get(id)) {
                menuModel= this.get(id);
                currentValue=menuModel.attributes;
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
            return null;
        }

    });
});