/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Router.extend({

        initialize : function (options) {
            var currentRouter = this;
            this.document = options.document;
            this.urlFragmentTemplate = _.template("<%= path %>?app=DOCUMENT&mode=<%= mode %>&id=<%= initid %><% if (revision >= 0) { %>&revision=<%= revision %><% } %><% if (viewId) { %>&vid=<%= viewId %><% } %>");

            this.route(/[^?]*\?app=DOCUMENT([^#]+)/, "fetch");
            // Listen to document sync and update url
            this.document.listenTo(this.document, "sync", function sync() {
                var viewId = currentRouter.document.get("viewId"),
                    options = {
                        "path" :     window.location.pathname,
                        "initid" :   currentRouter.document.get("initid"),
                        "revision" : currentRouter.document.get("revision") >= 0 ? currentRouter.document.get("revision") : undefined,
                        "mode" :     currentRouter.document.get("renderMode"),
                        "viewId" :   undefined
                    };
                if (!_.isUndefined(viewId) && viewId !== "!defaultConsultation" && viewId !== "!defaultEdition") {
                    options.viewId = viewId;
                }
                if (window.dcp.viewData.documentIdentifier === options.initid &&
                    window.dcp.viewData.revision === options.revision &&
                    window.dcp.viewData.vid === options.viewId) {
                    return;
                }
                if (options.initid) {
                    currentRouter.navigate(currentRouter.urlFragmentTemplate(options));
                }
            });
        },

        fetch : function fetch(searchPart) {
            var i, split, queries = searchPart.split('&'), searchObject = {}, newValues = {};
            for (i = 0; i < queries.length; i++) {
                split = queries[i].split('=');
                searchObject[split[0]] = decodeURIComponent(split[1]);
            }
            if (!_.isUndefined(searchObject.id)) {
                newValues.initid = searchObject.id;
            }
            if (!_.isUndefined(searchObject.revision)) {
                newValues.revision = parseInt(searchObject.revision, 10);
            } else {
                newValues.revision = -1;
            }
            if (!_.isUndefined(searchObject.vid)) {
                newValues.viewId = searchObject.vid;
            } else {
                newValues.viewId = undefined;
            }
            if (!_.isUndefined(searchObject.mode)) {
                newValues.renderMode = searchObject.mode;
            }
            this.document.set(newValues).fetch();
        }

    });

});
