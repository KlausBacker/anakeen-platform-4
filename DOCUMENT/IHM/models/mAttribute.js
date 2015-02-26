/*global define*/
define([
    'underscore',
    'jquery',
    'backbone'
], function (_, $, Backbone) {
    'use strict';

    return Backbone.Model.extend({

        defaults : {
            title :            null,
            isValueAttribute : false,
            parent :           undefined,
            mode :             "read",
            errorMessage :     null
        },

        initialize : function mAttributeinitialize() {
            this.listenTo(this, "change:documentMode", this._computeMode);
            this.listenTo(this, "change:visibility", this._computeMode);
            this._computeValueMode();
            this._computeMode();
            this.set("title", this.id + '(' + this.get("label") + ')');
            this.set("errorMessage", null);
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
            content.content = [];
            if (this.get("content")) {
                content.content = this.get("content").toData();
            }
            return content;
        },

        isDisplayable : function mAttributeisDisplayable() {
            if (this.get("mode") === "hidden") {
                return false;
            }
            if (this.get("isValueAttribute")) {
                if (this.get("mode") === "read") {
                    if (this.getOption('showEmptyContent') !== null) {
                        return true;
                    } else {
                        return (this.get("attributeValue") && this.get("attributeValue").value !== null);
                    }
                } else if (this.get("mode") === "write") {
                    return true;
                }
            }
            if (this.get("content").length === 0) {
                return false;
            }
            if (this.getOption('showEmptyContent') === null) {
                if (this.get("content").some) {
                    return this.get("content").some(function (value) {
                        return value.isDisplayable();
                    });
                }
                return false;
            }
            return true;
        },

        hasMultipleOption : function mAttributehasMultipleOption() {
            return (this.attributes.options && this.attributes.options.multiple === "yes");
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
                if (this.get("isValueAttribute") && (_.isEmpty(this.get("attributeValue")) || _.isUndefined(this.get("attributeValue") || this.get("attributeValue").value === null))) {
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
            var optionsCommon={}, optionsValue={}, optionsAttribute={}, renderOptions, labels={};
            this._options = this._options || false;

            if (this._options === false) {
                renderOptions = this.collection.renderOptions;
                if (renderOptions.common) {
                    optionsCommon = renderOptions.common || {};
                    labels= _.clone(optionsCommon.labels) || {};
                }

                if (renderOptions.types) {
                    optionsValue = renderOptions.types[this.get("type")] || {};
                    labels= _.extend(labels, _.clone(optionsValue.labels));
                }
                if (renderOptions.attributes) {
                    optionsAttribute = renderOptions.attributes[this.id] || {};

                }
                // labels must be merged
                optionsAttribute.labels= _.extend(labels, optionsAttribute.labels);
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

        /**
         * Set the value of an option
         *
         * @param key
         * @param value
         * @returns {*}
         */
        setOption : function mAttributesetOption(key, value) {
            var options = this.getOptions();
            options[key] = value;
            this._options = options;
            this.trigger("optionModified", key);
            return this;
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
            return true;
        },

        haveView : function mAttributeHaveView() {
            var view = {haveView : false};
            this.trigger("haveView", view);
            return view.haveView;
        }

    });
});
