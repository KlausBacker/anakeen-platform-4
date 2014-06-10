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
            this.listenTo(this, "change:documentMode", this._computeMode);
            this.listenTo(this, "change:visibility", this._computeMode);
            this.listenTo(this, "change:type", this._computeValueMode);
            if (_.isArray(this.get("value"))) {
                this.set("value", _.extend({}, this.get("value")));
            }
            this._computeValueMode();
            this._computeMode();
        },

        setContentCollection : function(attributes) {
            var content = this.get("content"), collection = new CollectionContentAttributes();
            _.each(content, function (currentChild) {
                collection.push(attributes.get(currentChild.id));
            });
            this.set("content", collection);
        },

        setValue : function(value, index) {
            var currentValue;
            if (this.get("multiple") && !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id "+this.id);
            }
            currentValue = _.clone(this.get("value"));
            if (this.get("multiple")) {
                currentValue[index] = value;
                this.set("value", currentValue);
                return;
            }
            this.set("value", value);
        },

        removeLine : function(index) {
            var currentValue, oldValue;
            if (!this.get("multiple") || !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            oldValue = this.get("value");
            currentValue = _.clone(this.get("value"));
            _.each(currentValue, function(value, currentIndex) {
                currentIndex = parseInt(currentIndex, 10);
                if (currentIndex === index) {
                    delete currentValue[index];
                }
                if (currentIndex > index && oldValue[currentIndex]) {
                    delete currentValue[currentIndex];
                    currentValue[currentIndex - 1] = oldValue[currentIndex];
                }
            });
            this.set("value", currentValue);
        },

        getNbLines : function() {
            var nbLines = 0;
            if (!this.get("multiple")) {
                return -1;
            }
            _.each(this.get("value"), function(value, index) {
                if (index > nbLines) {
                    nbLines = index;
                }
            });
            return nbLines;
        },

        toData : function(index) {
            var content = this.toJSON();
            if (index && this.get("multiple") === false){
                throw new Error("You need to be multiple");
            }
            if (_.isNumber(index)) {
                content.value = content.value ? content.value[index] : null;
                content.index = index;
            }
            content.isDisplayable = this.isDisplayable();
            content.content = this.get("content").toData();
            return content;
        },

        isDisplayable : function() {
            if (this.get("mode") === "hidden") {
                return false;
            }
            if (this.get("valueAttribute")) {
                return true;
            }
            if (this.get("content").length === 0) {
                return false;
            }
            return this.get("content").some(function (value) {
                return value.isDisplayable();
            });
        },

        _computeMode : function () {
            var visibility = this.get("visibility"), documentMode = this.get("documentMode");
            if (visibility === "H") {
                this.set("mode", "hidden");
                return;
            }
            if (documentMode === "view") {
                if (visibility === "O" || (this.get("valueAttribute") && !this.get("value"))) {
                    this.set("mode", "hidden");
                    return;
                }
                this.set("mode", "read");
                return;
            }
            if (documentMode === "edit") {
                if (visibility === "W" || visibility === "O") {
                    this.set("mode", "write");
                    return;
                }
                if (visibility === "R" ) {
                    this.set("mode", "hidden");
                    return;
                }
                if (visibility === "R" || visibility === "S") {
                    this.set("mode", "read");
                    return;
                }
                if (visibility === "U") {
                    this.set("mode", "write");
                    this.set("addTool", false);
                    return;
                }
            }
            throw new Error("unkown mode " + documentMode + " or visibility " + visibility);
        },

        _computeValueMode : function () {
            var type = this.get("type");
            if (type === "frame" || type === "array" || type === "tab") {
                this.set("valueAttribute", false);
            }
        }

    });
});