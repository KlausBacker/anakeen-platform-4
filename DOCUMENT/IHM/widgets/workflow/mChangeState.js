/*global define*/
define([
    'underscore',
    'backbone',
    'models/mDocument'
], function (_, Backbone, mDocument) {
    'use strict';

    return mDocument.extend({

        defaults: {
            documentId: undefined,
            documentModel: undefined,
            state: undefined,
            attributes: []
        },
        /**
         * Initialize event handling
         *
         */
        initialize2: function mDocumentinitialize() {
            //mDocument.prototype.initialize.apply(this, arguments);
            //  this.listenTo(this, "error", _.bind(this.propagateSynchroError, this.get("documentModel")));
            // this.listenTo(this, "error", this.viewError);
        },

        /**
         * Compute the REST URL for the current document
         *
         * Used internaly by backbone in fetch, save, destroy
         *
         * @returns {string}
         */
        url: function mChangeStateurl() {
            var urlData = "api/v1/documents/<%= documentId %>/views/states/<%= state %>";

            urlData = urlData.replace("<%= documentId %>", encodeURIComponent(this.get("documentId")));
            urlData = urlData.replace("<%= transition %>", encodeURIComponent(this.get("transition")));
            urlData = urlData.replace("<%= state %>", encodeURIComponent(this.get("state")));

            return urlData;
        },
        /**
         * Parse the return of the REST API
         * @param response
         * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
         */
        parse: function mChangeStatetParse(response) {
            var values, attributes, templates, renderOptions;
            var documentModel = this.get("documentModel");
            if (response.success === false) {
                throw new Error("Unable to get the data from change state");
            }
            attributes = [];

            renderOptions = response.data.renderOptions;

            if (!renderOptions) {
                renderOptions = documentModel.get("renderOptions");
            }
            if (response.data.transition && response.data.transition.askAttributes.length > 0) {

                _.each(response.data.transition.askAttributes, function (ask, index) {

                    attributes.push(ask);
                });
            }
            templates = documentModel.get("templates");

            if (response.data.templates) {
                templates.body = response.data.templates.body;
                _.each(response.data.templates.sections, function (tpl, tplIdx) {
                    templates.sections[tplIdx] = tpl;
                });
            }

            this.initialProperties = _.defaults({
                "renderMode": "edit",
                "viewId": "!changeState"
            }, {});

            console.log("WID", documentModel.get("properties"));
            values = {
                initid: null,//response.data.workflow.properties.initid, // set to null to send a POST (create) when save
                properties: response.data.workflow.properties,
                menus: [],
                viewId: "!changeState",
                locale: documentModel.get("locale").culture,
                renderMode: "edit",
                attributes: attributes,
                templates: documentModel.get("templates"),
                renderOptions: renderOptions,
                customCSS: [],
                customJS: [],
                messages: response.messages,
                workflow: {
                    transition: response.data.transition,
                    state: response.data.state,
                    labels: response.data.labels
                }
            };
            window.dcp.changeState = this;
            return values;
        },

        /**
         * Used by backbone for the save part
         * @returns {{document: {attributes: *, properties : *}}}
         */
        toJSON: function mChangeStatetoJSON() {
            var values = this.getValues();
            var to = {parameters: {}};

            _.each(values, function (value, aid) {
                if (aid === "_workflow_comment_") {
                    to.comment = (_.isObject(value)) ? value.value : '';
                } else {

                    if (_.isArray(value)) {
                        if (value.length > 0 && _.isArray(value[0])) {
                            // double multiple
                            to.parameters[aid] = _.map(value, function (aValue) {
                                return _.pluck(aValue, "value");
                            });
                        } else {
                            to.parameters[aid] = _.pluck(value, "value");
                        }
                    } else if (_.isObject(value)) {
                        to.parameters[aid] = value.value;
                    } else {

                        to.parameters[aid] = value;

                    }
                }
            });
            return to;
        }
    });
});