/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache'
], function ($, _, Backbone, Mustache)
{
    'use strict';

    return Backbone.View.extend({

        className: "dcpDocument",

        /**
         * The current model is the document model
         * The header template comes from template "sections/header"
         */
        headerTemplate: null,

        initialize: function vHeaderInitialize()
        {
            this.listenTo(this.model.get("properties"), 'change', this.updateHeader);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'changeValue', this.documentHasChanged);
            this.headerTemplate = this.getTemplates("sections").header;
        },

        /**
         * apply mustache template to inner content
         * @returns {*}
         */
        render: function vheaderRender()
        {
            var data = this.model.toData();

            data.document.properties.security = data.document.properties.security || {lock: {lockedBy: null}};
            data.document.properties.security.lock.isLocked = (data.document.properties.security.lock.lockedBy && data.document.properties.security.lock.lockedBy.id > 0);

            var headerRender = $(Mustache.render(this.headerTemplate, data));
            var $header = this.$el;
            $header.empty();
            _.each(headerRender.children(), (function (elt)
            {
                $header.append(elt);
            }));

            $header.find(".dcpDocument__header__lock, .dcpDocument__header__readonly, .dcpDocument__header__modified").tooltip({
                placement:"bottom",
                html:true
            });

            return this;
        },

        /**
         * reset mustache template
         * update window title also
         * @returns {*}
         */
        updateHeader: function vheaderUpdateHeader()
        {
            var doctitle = this.model.get("properties").get('title');
            if (doctitle) {
                window.document.title = doctitle;
            }
            return this.render();
        },

        getTemplates: function vheadergetTemplates(key)
        {
            var templates = {};
            if (this.model && this.model.get("templates")) {
                templates = this.model.get("templates");
            }
            if (templates[key]) {
                return templates[key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates[key]) {
                return window.dcp.templates[key];
            }
            throw new Error("Unknown template  " + key);
        },
        documentHasChanged :function vheaderdocumentHasChanged() {
            var wTitle=window.document.title.replace(/^\*+/g, "");

            if (this.model.hasAttributesChanged()) {
                this.$el.find(".dcpDocument__header__modified").show();
                window.document.title = "*" + wTitle;
            } else {
                this.$el.find(".dcpDocument__header__modified").hide();
                window.document.title = wTitle;
            }
        }

    });

});
