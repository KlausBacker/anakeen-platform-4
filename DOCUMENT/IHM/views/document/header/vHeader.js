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
            this.headerTemplate = window.dcp.templates.sections.header;
        },

        /**
         * apply mustache template
         * @returns {*}
         */
        render: function vheaderRender() {
            this.$el.empty().append($(Mustache.render(this.headerTemplate, this.model.toData())));
            return this;
        },

        /**
         * reset mustache template
         * @returns {*}
         */
        updateHeader: function vheaderUpdateHeader() {
            return this.render();
        }
    });

});