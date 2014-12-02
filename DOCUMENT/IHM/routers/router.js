/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Router.extend({
        routes : {
            ":initid/:revision/:viewid" : "fetch"
        },

        initialize : function (options) {
            var currentRouter = this;
            this.document = options.document;
            this.urlFragmentTemplate = _.template("<%= initid %>/<%= revision %>/<%= viewId %>");
            this.document.listenTo(this.document, "sync", function sync() {
                var options = {
                    "initid" :   currentRouter.document.get("initid"),
                    "revision" : currentRouter.document.get("revision"),
                    "viewId" :   currentRouter.document.get("viewId")
                };
                if (window.dcp.viewData.documentIdentifier === options.initid &&
                    window.dcp.viewData.revision === options.revision &&
                    window.dcp.viewData.vid === options.viewId) {
                    return;
                }
                currentRouter.navigate(currentRouter.urlFragmentTemplate(options));
            });
        },

        fetch : function fetch(initid, revision, viewId) {
            this.document.set({"initid" : initid, "revision" : revision, "viewId" : viewId}).fetch();
        }

    });

});