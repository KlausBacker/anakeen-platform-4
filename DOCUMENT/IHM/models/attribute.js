/*global define*/
define([
    'underscore',
    'backbone',
    'collections/contentAttributes'
], function (_, Backbone, CollectionContentAttributes) {
    'use strict';

    return Backbone.Model.extend({

        defaults: {
            parent: undefined,
            content: [],
            valueAttribute: true,
            multiple: false,
            mode: "read",
            documentMode: "read",
            errorMessage: null
        },

        initialize: function () {
            this.listenTo(this, "change:documentMode", this._computeMode);
            this.listenTo(this, "change:visibility", this._computeMode);
            this.listenTo(this, "change:type", this._computeValueMode);
            if (_.isArray(this.get("value"))) {
                this.set("value", _.extend({}, this.get("value")));
            }
            this._computeValueMode();
            this._computeMode();
        },

        setContentCollection: function (attributes) {
            var content = this.get("content"), collection = new CollectionContentAttributes();
            _.each(content, function (currentChild) {
                collection.push(attributes.get(currentChild.id));
            });
            this.set("content", collection);
        },

        setValue: function (value, index) {
            var currentValue;
            if (this.get("multiple") && !_.isNumber(index) && !_.isArray(value)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            if (this.get("multiple") && index >= 0) {
                currentValue = _.clone(this.get("value"));
                currentValue[index] = value;

                this.set("value", currentValue);

            } else {
                this.set("value", value);
            }
        },

        addValue: function (value, index) {
            console.log("add value", index);
            var currentValue;
            if (this.get("multiple") && !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            // clone array references
            currentValue = _.toArray(_.map(this.get("value"), _.clone));

            if (this.get("multiple") && index >= 0) {
                currentValue[index].push(value);
                currentValue=_.extend({},currentValue);
                this.set("value", currentValue);
            } else {
                currentValue.push(value);
                this.set("value", currentValue);
            }
        },

        removeIndexValue: function (index) {
            var currentValue, oldValue;
            if (!this.get("multiple") || !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            console.log("remove line", index, this.id);
            oldValue = this.get("value");
            currentValue = _.clone(this.get("value"));
            _.each(currentValue, function (value, currentIndex) {
                currentIndex = parseInt(currentIndex, 10);
                if (currentIndex === index) {
                    delete currentValue[index];
                }
                if (currentIndex > index && oldValue[currentIndex]) {
                    delete currentValue[currentIndex];
                    currentValue[currentIndex - 1] = oldValue[currentIndex];
                }
            });
            this.set("value", currentValue, {silent:true});
        },
        addIndexValue: function (index) {
            var currentValue, oldValue;
            if (!this.get("multiple") || !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            console.log("add line", index, this.id);
            oldValue = this.get("value");
            console.log("add line", oldValue);
           // currentValue = _.clone(this.get("value"));
            currentValue = _.toArray(_.map(this.get("value"), _.clone));
            var firstValue= _.clone(currentValue[0]);
            console.log("add new item", firstValue);
            currentValue.push(firstValue);
            console.log("add new line", currentValue);

            this.set("value", currentValue, {silent:true});
        },

        getNbLines: function () {
            var nbLines = 0;
            if (!this.get("multiple")) {
                return -1;
            }
            _.each(this.get("value"), function (value, index) {
                if (index > nbLines) {
                    nbLines = index;
                }
            });
            return nbLines;
        },

        toData: function (index) {
            var content = this.toJSON();
            if (index && this.get("multiple") === false) {
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

        isDisplayable: function () {
            if (this.get("mode") === "hidden") {
                return false;
            }
            if (this.get("valueAttribute")) {
                return true;
            }
            if (this.get("content").length === 0) {
                return false;
            }
            if (this.getOption('showEmptyContent') === null) {
                return this.get("content").some(function (value) {
                    return value.isDisplayable();
                });
            }

            return true;

        },

        hasMultipleOption: function () {

            return (this.attributes.options && this.attributes.options.multiple === "yes");
        },
        inArray: function () {
            var aparent = this.getParent();
            return (aparent && aparent.attributes && aparent.attributes.type === "array");
        },

        documentModel: function () {
            return  window.dcp.documents.get(window.dcp.documentData.document.properties.id);
        },
        getParent: function () {
            if (this.attributes.parent) {
                return this.documentModel().get('attributes').get(this.attributes.parent);
            }
            return null;
        },

        _computeMode: function () {
            var visibility = this.get("visibility"), documentMode = this.get("documentMode");
            if (visibility === "H") {
                this.set("mode", "hidden");
                return;
            }
            if (documentMode === "view") {
                if (visibility === "O") {
                    this.set("mode", "hidden");
                    return;
                }

                if (this.get("valueAttribute") && this.get("value").value === null) {
                    if (this.getOption('showEmptyContent') === null) {
                        this.set("mode", "hidden");
                        return;
                    } else {
                        // Modify displayValue with showEmptyContent option
                        var gv = this.get("value");
                        gv.emptyValue = this.getOption('showEmptyContent');
                        this.set("value", gv);
                    }
                }
                this.set("mode", "read");
                return;
            }
            if (documentMode === "edit") {
                if (visibility === "W" || visibility === "O") {
                    this.set("mode", "write");
                    return;
                }
                if (visibility === "R") {
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

        _computeValueMode: function () {
            var type = this.get("type");
            if (type === "frame" || type === "array" || type === "tab") {
                this.set("valueAttribute", false);
            }
        },

        /**
         * Return all options for an attribute
         * @TODO add local cache
         * @returns {{}}
         */
        getOptions: function () {
            var options = {};
            var optionCommon = window.dcp.renderOptions.common || {};
            var optionValue = {};
            var optionAttribute = {};

            if (window.dcp.renderOptions.types) {
                optionValue = window.dcp.renderOptions.types[this.get("type")] || {};
            }
            if (window.dcp.renderOptions.attributes) {
                optionAttribute = window.dcp.renderOptions.attributes[this.id] || {};
            }

            _.extend(options, optionCommon, optionValue, optionAttribute);

            return options;
        },

        /**
         * Get value for an option
         * @TODO set local cache
         * @param key option identifier
         * @returns {*}
         */
        getOption: function (key) {

            var options = this.getOptions();
            if (typeof options[key] !== "undefined") {
                return options[key];
            }
            return null;
        },

        setErrorMessage: function (message, index) {
            if (this.get("multiple") && typeof index !== "undefined") {
                var errorMessage = this.get('errorMessage') || [];
                // delete duplicate
                _.reject(errorMessage, function (mindex) {
                    return mindex === index;
                });
                errorMessage.push({message: message, index: index});
                this.set('errorMessage', _.clone(errorMessage));
            } else {
                this.set('errorMessage', message);
            }
        }

    });
});