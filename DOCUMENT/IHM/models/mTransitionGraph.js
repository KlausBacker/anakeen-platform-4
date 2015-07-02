/*global define*/
define([
    'underscore',
    'backbone',
    'dcpDocument/models/mDocument'
], function (_, Backbone, mDocument) {
    'use strict';

    return Backbone.Model.extend({

        typeModel:"ddui:transitionGraph",
        defaults: {
            documentId: undefined,
            state: undefined
        },

        /**
         * Compute the REST URL for the current document
         *
         * Used internaly by backbone in fetch, save, destroy
         *
         * @returns {string}
         */
        url: function mTransition_url() {
            var urlData = "api/v1/documents/<%= documentId %>/workflows/states/?allStates=1";

            urlData = urlData.replace("<%= documentId %>", encodeURIComponent(this.get("documentId")));

            return urlData;
        },
        /**
         * Parse the return of the REST API
         * @param response
         * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
         */
        parse: function mTransition_Parse(response) {
            var values;
            if (response.success === false) {
                throw new Error("Unable to get the data from change state");
            }

            values = {
                state: this.get("state"),
                messages: response.messages,
                workflowStates: response.data.states
            };
            return values;
        }

    });
});