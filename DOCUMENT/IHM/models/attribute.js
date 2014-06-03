/*global define*/
define([
    'underscore',
    'backbone',
    'collections/contentAttributes'
], function (_, Backbone, CollectionContentAttributes) {
    'use strict';

    return Backbone.Model.extend({

        defaults : {
            parent : undefined,
            content :       [],
            valueAttribute : true,
            multiple :       false,
            mode : "read",
            documentMode : "read"
        },

        initialize : function() {
            this.listenTo(this, "change:documentMode", this.computeMode);
            this.listenTo(this, "change:visibility", this.computeMode);
            this.listenTo(this, "change:type", this.computeValueMode);
            this.computeMode();
            this.computeValueMode();
        },

        setContentCollection : function(attributes) {
            var content = this.get("content"), collection = new CollectionContentAttributes();
            _.each(content, function (currentChild) {
                collection.push(attributes.get(currentChild.id));
            });
            this.set("content", collection);
        },

        computeMode : function() {
            var visibility = this.get("visibility"), documentMode = this.get("documentMode");
            if (visibility === "H") {
                this.set("mode", "hidden");
                return;
            }
            if (documentMode === "view") {
                if (visibility === "W" || visibility === "O" || visibility === "R" || visibility === "S") {
                    this.set("mode", "read");
                    return;
                }
            }
            if (documentMode === "edit") {
                if (visibility === "W" || visibility === "O") {
                    this.set("mode", "write");
                    return;
                }
                if (visibility === "R" || visibility === "S") {
                    this.set("mode", "read");
                    return;
                }
            }
            throw "unkown mode "+documentMode+" or visibility "+visibility;
        },

        computeValueMode : function() {
            var type = this.get("type");
            if (type === "frame" || type === "array" || type === "tab") {
                this.set("valueAttribute", false);
            }
        }

    });
});