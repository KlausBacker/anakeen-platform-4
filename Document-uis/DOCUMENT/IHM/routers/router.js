/*global define*/
/*global define*/
define([
    'underscore',
    'backbone'
], function define_router(_, Backbone) {
    'use strict';

    return Backbone.Router.extend({

        initialize : function router_initialize(options) {
            var currentRouter = this;
            this.document = options.document;
            this.urlFragmentTemplate = _.template("<%= path %>?app=DOCUMENT&initid=<%= initid %><% if (revision >= 0) { %>&revision=<%= revision %><% } %><% if (viewId) { %>&viewId=<%= viewId %><% } %>");

            this.route(/[^?]*\?app=DOCUMENT([^#]+)/, "fetch");
            // Listen to document sync and update url
            this.document.listenTo(this.document, "sync", function sync() {
                var viewId = currentRouter.document.get("viewId"),
                    options = {
                        "path" :     window.location.pathname,
                        "initid" :   currentRouter.document.get("initid"),
                        "revision" : currentRouter.document.get("revision") >= 0 ? currentRouter.document.get("revision") : undefined,
                        "viewId" :   undefined
                    };
                var docProperties=currentRouter.document.getServerProperties();
                options.viewId = viewId;
                if (docProperties && docProperties.status === "alive") {
                    // No write revision if not a fixed one
                    options.revision=-1;
                }
                if (window.dcp && window.dcp.viewData && window.dcp.viewData.initid === options.initid &&
                    window.dcp.viewData.revision === options.revision &&
                    window.dcp.viewData.viewId === options.viewId) {
                    return;
                }
                if (options.initid) {
                    window.dcp.viewData.revision = options.revision;
                    window.dcp.viewData.viewId = options.viewId;
                    window.dcp.viewData.initid = options.initid;
                    currentRouter.navigate(currentRouter.urlFragmentTemplate(options));
                }
            });
        },

        fetch : function router_fetch(searchPart) {
            var i, split, queries = searchPart.split('&'), searchObject = {}, newValues = {};
            for (i = 0; i < queries.length; i++) {
                split = queries[i].split('=');
                searchObject[split[0]] = decodeURIComponent(split[1]);
            }
            if (!_.isUndefined(searchObject.id)) {
                newValues.initid = searchObject.id;
            }
            if (!_.isUndefined(searchObject.initid)) {
                newValues.initid = searchObject.initid;
            }
            if (!_.isUndefined(searchObject.revision)) {
                newValues.revision = parseInt(searchObject.revision, 10);
            } else {
                newValues.revision = -1;
            }
            if (!_.isUndefined(searchObject.viewId)) {
                newValues.viewId = searchObject.viewId;
            } else {
                newValues.viewId = undefined;
            }
            this.document.fetchDocument(newValues);
        }

    });

});
