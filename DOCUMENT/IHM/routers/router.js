/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Router.extend({
        routes : {
            "?app=DOCUMENT&mode=:mode&id=:initid&revision=:revision&vid=:viewId" : "fetch"
        },

        initialize : function (options) {
            var currentRouter = this;
            this.document = options.document;
            this.urlFragmentTemplate = _.template("<%= path %>?app=DOCUMENT&mode=<%= mode %>&id=<%= initid %>&revision=<%= revision %>&vid=<%= viewId %>");

            this.route(this.urlFragmentTemplate({
                "path" : window.location.pathname,
                "initid" : ":initid",
                "revision" : ":revision",
                "viewId" : ":viewId",
                "mode" : ":mode"
            }).substring(1), "fetch");
            // Listen to document sync and update url
            this.document.listenTo(this.document, "sync", function sync() {
                var options = {
                    "path" : window.location.pathname,
                    "initid" :   currentRouter.document.get("initid"),
                    "revision" : currentRouter.document.get("revision"),
                    "viewId" :   currentRouter.document.get("viewId"),
                    "mode" :     currentRouter.document.get("renderMode")
                };
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

        fetch : function fetch(mode, initid, revision, viewId) {
            this.document.set({"initid" : initid, "revision" : revision, "viewId" : viewId}).fetch();
        }

    });

});