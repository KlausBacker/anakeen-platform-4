/*global define*/
define([
    'underscore',
    'backbone',
    'mustache'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className: "dcpDocument",

        /**
         * The current model is the document model
         * The header template comes from template "sections/header"
         */
        headerTemplate: null,

        initialize: function vHeaderInitialize() {
            this.listenTo(this.model.get("properties"), 'change', this.updateHeader);
            this.listenTo(this.model, 'destroy', this.remove);
            this.headerTemplate = this.getTemplates("sections").header;
        },

        /**
         * apply mustache template to inner content
         * @returns {*}
         */
        render: function vheaderRender() {
            var headerRender=$(Mustache.render(this.headerTemplate, this.model.toData()));
            var $header=this.$el;
            $header.empty();
            _.each(headerRender.children(), (function (elt) {
                $header.append(elt);
            }));
            return this;
        },

        /**
         * reset mustache template
         * update window title also
         * @returns {*}
         */
        updateHeader: function vheaderUpdateHeader() {
            var doctitle=this.model.get("properties").get('title');
            if (doctitle) {
                window.document.title=doctitle;
            }
            return this.render();
        },

        getTemplates : function getTemplates(key) {
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
        }
    });

});