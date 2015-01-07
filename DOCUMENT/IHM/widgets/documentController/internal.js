define([
    'jquery',
    'underscore',
    'backbone',
    'routers/router',
    'models/mDocument',
    'views/document/vDocument',
    'widgets/widget',
    'widgets/window/wConfirm',
    'widgets/window/wLoading'
], function ($, _, Backbone, Router, DocumentModel, DocumentView) {
    'use strict';

    $.widget("dcp.documentController", {

        options : {
            eventPrefix : "document",
            initid :      null,
            viewId :      undefined,
            revision :    undefined
        },

        /**
         * Create widget
         * @private
         */
        _create : function documentController_create() {
            if (!this.options.initid) {
                throw new Error("Widget cannot be initialized without an initid");
            }
            this._initExternalElements();
            this._initModel(this._getModelValue());
            this._initView();
            this._model.fetch();
            this._initRouter();
            this._super();
        },

        /**
         * Delete the widget
         * @private
         */
        _destroy : function documentController_destroy() {
            this.view.remove();
            delete this._model;
            this._trigger("destroy");
            this._super();
        },

        /**
         * Return essential element of the current document
         *
         * @returns {Object}
         * @private
         */
        _getModelValue : function documentController_getModelValue() {
            return _.pick(this.options, "initid", "viewId", "revision");
        },

        /**
         * Generate the dom where the view is inserted
         * @private
         */
        _initDom : function documentController_initDom() {
            var $document = this.element.find(".dcpDocument");
            if (!this.$document || $document.length === 0) {
                this.element.append('<div class="dcpDocument"></div>');
                this.$document = this.element.find(".dcpDocument");
            }
        },

        /**
         * Init the model and bind the events
         *
         * @param initialValue
         * @returns {DocumentModel}
         * @private
         */
        _initModel : function documentController_initModel(initialValue) {
            var model = new DocumentModel(initialValue);
            this._model = model;
            this._initModelEvents();
            return model;
        },

        /**
         * Init the view and bind the events
         *
         * @returns {DocumentView}
         * @private
         */
        _initView : function documentController_initView() {
            var documentView, $document;
            this._initDom();
            $document = this.$document;
            documentView = new DocumentView({model : this._model, el : $document[0]});
            this.view = documentView;
            this._initViewEvents();
            return documentView;
        },

        /**
         * Clear and reinit the model with current widget values
         *
         * @private
         */
        _reinitModel : function documentController_reinitModel() {
            this._model.clear().set(this._getModelValue());
        },

        /**
         * Init the external elements (loading bar and notification widget)
         * @private
         */
        _initExternalElements : function documentController_initExternalElements() {
            this.$loading = $(".dcpLoading").dcpLoading();
            this.$notification = $('body').dcpNotification(); // active notification
        },

        /**
         * Bind the model event
         *
         * Re-trigger the event
         *
         * @private
         */
        _initModelEvents : function documentController_initEvents() {
            var currentWidget = this;
            this._model.listenTo(this._model, "invalid", function showInvalid(model, error) {
                currentWidget.$notification.dcpNotification("showError", error);
                currentWidget._trigger("error", {}, {
                    documentData : currentWidget._model.getDocumentData(),
                    message :      error
                });
            });
            this._model.listenTo(this._model, "showError", function showError(error) {
                currentWidget.$notification.dcpNotification("showError", error);
                currentWidget._trigger("error", {}, {
                    documentData : currentWidget._model.getDocumentData(),
                    message :      error
                });
            });
            this._model.listenTo(this._model, "sync", function () {
                currentWidget.options.initid = currentWidget._model.id;
                currentWidget.options.viewId = currentWidget._model.get("viewId");
                currentWidget.options.revision = currentWidget._model.get("revision");
                currentWidget.element.data(currentWidget._getModelValue());
            });
            this._model.listenTo(this._model, "fetch", function () {
                currentWidget._trigger("close", {}, {
                    documentData : currentWidget._model.getDocumentData()
                });
            });
            this._model.listenTo(this._model, "changeValue", function (options) {
                currentWidget._trigger("change", {}, {
                    documentData : currentWidget._model.getDocumentData(),
                    change :       options
                });
            });
        },

        /**
         * Bind the view
         * Re-trigger the events
         *
         * @private
         */
        _initViewEvents : function documentController_initViewEvents() {
            var currentWidget = this;
            this.view.on("cleanNotification", function () {
                currentWidget.$notification.dcpNotification("clear");
            });
            this.view.on('loading', function (data) {
                currentWidget.$loading.dcpLoading('setPercent', data);
            });
            this.view.on('loaderShow', function () {
                console.time("xhr+render document view");
                currentWidget.$loading.dcpLoading('show');
            });
            this.view.on('loaderHide', function () {
                currentWidget.$loading.dcpLoading('hide');
            });
            this.view.on('partRender', function () {
                currentWidget.$loading.dcpLoading('addItem');
            });
            this.view.on('renderDone', function () {
                console.timeEnd("xhr+render document view");
                currentWidget._trigger("ready", {}, {
                    documentData : currentWidget._model.getDocumentData()
                });
                currentWidget.$loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
                _.delay(function () {
                    currentWidget.$loading.dcpLoading("hide");
                    console.timeEnd('main');
                }, 250);
            });
            this.view.on("showMessage", function showMessage(message) {
                currentWidget.$notification.dcpNotification("show", message.type, message);
                currentWidget._trigger("message", {}, {
                    documentData : currentWidget._model.getDocumentData(),
                    message :      message
                });
            });
            this.view.on("showSuccess", function showSuccess(message) {
                currentWidget.$notification.dcpNotification("showSuccess", message);
                currentWidget._trigger("message", {}, {
                    documentData : currentWidget._model.getDocumentData(),
                    message :      message
                });
            });
            this.view.on("reinit", function reinit() {
                currentWidget._initView();
                currentWidget.model.fetch();
            });
        },

        /**
         * Init the pushstate router
         *
         * @private
         */
        _initRouter : function documentController_initRouter() {
            Backbone.history.start({pushState : true});
            this.router = new Router({document : this._model});
        },

        _getAttributeModel : function documentController_getAttributeModel(attributeId) {
            var attribute = this._model.get("attributes").get(attributeId);
            if (!attribute) {
                throw new Error("The attribute " + attributeId + " doesn't exist");
            }
            return attribute;
        },

        _getMaxIndex : function documentController_getMaxIndex(attributeArray) {
            return _.size(attributeArray.get("content").max(function (currentAttr) {
                return _.size(currentAttr.get("value"));
            }).get("value"));
        },

        reinitDocument : function documentControllerReinitDocument() {
            this._reinitModel();
            this._model.fetch();
        },

        fetchDocument : function documentControllerFetchDocument(options) {
            options = _.isUndefined(options) ? {} : options;
            if (!_.isObject(options)) {
                throw new Error('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
            }
            options = _.defaults(options, {
                "revision" : -1,
                "viewId" :   "!defaultConsultation"
            });
            this.options = _.defaults(options, this.options);
            this.reinitDocument();
        },

        getProperty : function documentControllerGetDocumentProperty(property) {
            return this._model.get("properties").get(property);
        },

        getProperties : function documentControllerGetDocumentProperties() {
            return this._model.get("properties").toJSON();
        },

        hideAttribute : function documentControllerHideAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("hide");
        },

        showAttribute : function documentControllerShowAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("show");
        },

        getValue : function documentControllerGetValue(attributeId) {
            var attribute = this._getAttributeModel(attributeId);
            return attribute.get("value");
        },

        getValues : function documentControllerGetValues() {
            return this._model.getValues();
        },

        setValue : function documentControllerSetValue(attributeId, value) {
            var attribute = this._getAttributeModel(attributeId);
            if (!_.isObject(value)) {
                throw new Error("Value must be an object with value and displayValue properties");
            }
            value = _.defaults(value, {value : "", displayValue : ""});
            return attribute.set("value", value);
        },

        appendArrayRow : function documentControllerAddArrayRow(attributeId, values) {
            var attribute = this._getAttributeModel(attributeId);
            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for "+attributeId);
            }
            attribute.get("content").each(function addACell(currentAttribute) {
                var currentValue = values[currentAttribute.id];
                if (_.isUndefined(currentValue)) {
                    return;
                }
                currentValue = _.defaults(currentValue, {value : "", displayValue : ""});
                currentAttribute.addValue(currentValue);
            });
        },

        insertBeforeArrayRow : function documentControllerInsertBeforeArrayRow(attributeId, values, index) {
            var attribute = this._getAttributeModel(attributeId), maxValue;
            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for " + attributeId);
            }
            maxValue = this._getMaxIndex(attribute);
            if ( index < 0 || index > maxValue) {
                throw new Error("Index must be between 0 and "+maxValue);
            }
            attribute.get("content").each(function addACell(currentAttribute) {
                var currentValue = values[currentAttribute.id];
                if (!_.isUndefined(currentValue)) {
                    currentValue = _.defaults(currentValue, {value : "", displayValue : ""});
                }
                currentAttribute.addIndexedLine(index, currentValue);
            });
        },

        removeArrayRow : function documentControllerRemoveArrayRow(attributeId, index) {
            var attribute = this._getAttributeModel(attributeId), maxIndex;
            if (attribute.get("type") !== "array") {
                throw Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            maxIndex = this._getMaxIndex(attribute) - 1;
            if (index < 0 || index > maxIndex) {
                throw Error("Index must be between 0 and "+maxIndex+" for " + attributeId);
            }
            attribute.get("content").each(function removeACell(currentAttribute) {
                currentAttribute.removeIndexValue(index);
            });
            attribute.removeIndexedLine(index);
        }

    });

    return $.fn.documentController;
});