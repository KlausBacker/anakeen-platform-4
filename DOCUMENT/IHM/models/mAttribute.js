/*global define*/
define([
    'underscore',
    'jquery',
    'backbone',
    'collections/contentAttributes',
    'models/constraintHandler'
], function (_, $, Backbone, CollectionContentAttributes, ConstraintHandler) {
    'use strict';

    return Backbone.Model.extend({

        defaults : {
            parent :         undefined,
            content :        [],
            isValueAttribute : true,
            multiple :       false,
            mode :           "read",
            documentMode :   "read",
            errorMessage :   null,
            title :          null,
            documentModel :  null
        },

        initialize : function mAttributeinitialize() {
            this.listenTo(this, "change:documentMode", this._computeMode);
            this.listenTo(this, "change:visibility", this._computeMode);
            this.listenTo(this, "change:attributeValue", this.checkConstraint);

            this._computeValueMode();
            this._computeMode();
            this.set("title", this.id + '(' + this.get("label") + ')');
        },

        setContentCollection : function mAttributesetContentCollection(attributes) {
            var content = this.get("content"), collection = new CollectionContentAttributes();
            _.each(content, function (currentChild) {
                collection.push(attributes.get(currentChild.id));
            });
            this.set("content", collection);
        },

        setValue : function mAttributesetValue(value, index) {
            var currentValue;
            if (this.get("multiple") && !_.isNumber(index) && !_.isArray(value)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            if (this.get("multiple") && index >= 0) {
                currentValue = _.clone(this.get("attributeValue"));
                currentValue[index] = value;
                this.set("attributeValue", currentValue);
            } else {
                this.set("attributeValue", value);
            }
        },

        addValue : function mAttributeaddValue(value, index) {
            var currentValue;
            if (this.hasMultipleOption() && !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            // clone array references
            currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));

            if (this.hasMultipleOption() && index >= 0) {
                //Init the multiple value if void
                if (!currentValue[index]) {
                    currentValue[index] = [];
                }
                currentValue[index].push(value);
                this.set("attributeValue", currentValue);
            } else {
                currentValue.push(value);
                this.set("attributeValue", currentValue);
            }
        },

        removeIndexValue : function mAttributeremoveIndexValue(index, options) {
            var currentValue, oldValue;
            if (!this.get("multiple") || !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            oldValue = this.get("attributeValue");
            currentValue = _.clone(this.get("attributeValue"));
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
            currentValue = _.filter(currentValue, function removeUndefined(currentValue) {
                return !_.isUndefined(currentValue);
            });
            this.set("attributeValue", currentValue, {updateArray : true});
        },

        /**
         * Add an indexed value with or without default value
         * Used by attributes in array to add new line or duplicate line
         *
         * @param index
         * @param copy
         */
        createIndexedValue : function mAttributeCreateIndexedValue(index, copy) {
            var currentValue, defaultValue;
            var newValue;
            if (!this.get("multiple") || !_.isNumber(index)) {
                throw new Error("You need to add an index to set value for a multiple id " + this.id);
            }
            currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));
            defaultValue = this.attributes.defaultValue;
            if (copy) {
                newValue = _.clone(currentValue[index]);
            } else if (defaultValue) {
                newValue = defaultValue;
            } else if (this.hasMultipleOption()) {
                newValue = [];
            } else {
                newValue = {value : null, displayValue : ''};
            }

            if (index > currentValue.length) {
                currentValue.push(newValue);
            } else {
                currentValue.splice(index, 0, newValue);
            }
            this.set("attributeValue", currentValue, {updateArray : true});
        },

        /**
         * Add values to indexed element
         * Used by attributes in array to add new line or duplicate line
         *
         * @param newValue
         * @param index
         */
        addIndexedValue : function mAttributeAddIndexedValue(newValue, index) {
            var currentValue;
            if (!_.isNumber(index)) {
                throw new Error("You need to add an index to set value indexed value " + this.id);
            }
            currentValue = _.toArray(_.map(this.get("attributeValue"), _.clone));

            if (index > currentValue.length) {
                currentValue.push(newValue);
            } else {
                currentValue.splice(index, 0, newValue);
            }
            this.set("attributeValue", currentValue);
        },

        /**
         * move a value in multiple value attribute
         * @param fromIndex
         * @param toIndex
         */
        moveIndexValue : function mAttributemoveIndexValue(fromIndex, toIndex) {
            var currentValue, fromValue;
            if (!this.get("multiple")) {
                throw new Error("Move only multiple attribute : " + this.id);
            }
            currentValue = _.toArray(this.get("attributeValue"));
            fromValue = _.clone(currentValue[fromIndex]);

            currentValue.splice(fromIndex, 1);
            currentValue.splice(toIndex, 0, fromValue);

            this.set("attributeValue", currentValue);
            this.trigger("moved", {from: fromIndex, to: toIndex});

        },

        getNbLines : function mAttributegetNbLines() {
            var nbLines = 0;
            if (!this.get("multiple")) {
                return -1;
            }
            _.each(this.get("attributeValue"), function (value, index) {
                if (index > nbLines) {
                    nbLines = index;
                }
            });
            return nbLines;
        },

        toData : function mAttributetoData(index) {
            var content = this.toJSON();
            if (index && this.get("multiple") === false) {
                throw new Error("You need to be multiple");
            }
            if (_.isNumber(index)) {
                content.attributeValue = content.attributeValue ? content.attributeValue[index] : null;
                content.index = index;
            }
            content.isDisplayable = this.isDisplayable();
            content.content = this.get("content").toData();
            return content;
        },

        isDisplayable : function mAttributeisDisplayable() {
            if (this.get("mode") === "hidden") {
                return false;
            }
            if (this.get("isValueAttribute")) {
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

        hasMultipleOption : function mAttributehasMultipleOption() {
            return (this.attributes.options && this.attributes.options.multiple === "yes");
        },

        isInArray : function mAttributeisInArray() {
            var aparent = this.getParent();
            return (aparent && aparent.attributes && aparent.attributes.type === "array");
        },

        getParent : function mAttributegetParent() {
            if (this.attributes.parent) {
                return this.getDocumentModel().get('attributes').get(this.attributes.parent);
            }
            return null;
        },

        _computeMode : function mAttribute_computeMode() {
            var visibility = this.get("visibility"), documentMode = this.collection.renderMode;
            if (visibility === "H" || visibility === "I") {
                this.set("mode", "hidden");
                return;
            }
            if (documentMode === "view") {
                if (visibility === "O") {
                    this.set("mode", "hidden");
                    return;
                }
                if (this.get("isValueAttribute") && (_.isEmpty(this.get("attributeValue")) || _.isUndefined(this.get("attributeValue") || this.get("attributeValue").value === null  ))) {
                    if (this.getOption('showEmptyContent') === null) {
                        this.set("mode", "hidden");
                        return;
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
            throw new Error("unkown mode " + documentMode + " or visibility " + visibility + " " + this.get("id"));
        },

        _computeValueMode : function mAttribute_computeValueMode() {
            var type = this.get("type"), visibility = this.get("visibility");
            if (type === "frame" || type === "array" || type === "tab" || visibility === "I") {
                this.set("isValueAttribute", false);
            }
        },
        /**
         * Return all options for an attribute
         *
         * @returns {{}}
         */
        getOptions :        function mAttributegetOptions() {
            var optionsCommon, optionsValue, optionsAttribute, renderOptions;
            this._options = this._options || false;

            if (this._options === false) {
                renderOptions = this.collection.renderOptions;
                if (renderOptions.common) {
                    optionsCommon = renderOptions.common || {};
                }

                if (renderOptions.types) {
                    optionsValue = renderOptions.types[this.get("type")] || {};
                }
                if (renderOptions.attributes) {
                    optionsAttribute = renderOptions.attributes[this.id] || {};
                }
                this._options = {};
            }

            _.extend(this._options, optionsCommon, optionsValue, optionsAttribute);

            return this._options;
        },

        /**
         * Get value for an option
         *
         * @param key option identifier
         * @returns {*}
         */
        getOption : function mAttributegetOption(key) {
            var options = this.getOptions();
            if (typeof options[key] !== "undefined") {
                return options[key];
            }
            return null;
        },

        getDocumentModel : function mAttributegetDocumentModelgetDocumentModel() {
            return this.collection.documentModel;
        },

        getTemplates : function mAttributegetTemplatesgetTemplates() {
            var templates = this.getDocumentModel().get("templates");
            if (!templates) {
                templates = window.dcp.templates;
            }
            return templates;
        },

        setErrorMessage : function mAttributesetErrorMessage(message, index) {
            if (this.get("multiple") && typeof index !== "undefined") {
                var errorMessage = this.get('errorMessage') || [];
                // delete duplicate
                _.reject(errorMessage, function (indexMessage) {
                    return indexMessage.index === index;
                });

                this.set('errorMessage', [{message : message, index : index}].concat(errorMessage));
            } else {
                this.set('errorMessage', message);
            }
        },

        checkConstraint : function mAttributecheckConstraint() {
            var response = new ConstraintHandler(), responseText;
            if (this.get("isValueAttribute") !== true) {
                return true;
            }
            this.trigger("constraint", {model : this, response : response, value : this.get("attributeValue")});
            if (response.hasConstraintMessages()) {
                responseText = "";
                _.each(response.getConstraintMessages(), function(currentResponse) {
                    responseText += currentResponse.message+" ";
                });
                this.setErrorMessage(responseText);
                return false;
            } else {
                this.setErrorMessage(null);
                return true;
            }
        }

    });
});
