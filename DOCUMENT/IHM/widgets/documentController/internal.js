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

    $.widget("dcp.documentInternal", {

        options : {
            eventPrefix : "dcpDocumentInternal",
            initid :      null,
            viewId :      undefined,
            revision :    undefined
        },

        _create : function _create() {
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

        _getModelValue : function _getModelValue() {
            return _.pick(this.options, "initid", "viewId", "revision");
        },

        _initDom : function _initDom() {
            var $document = this.element.find(".dcpDocument");
            if (!this.$document || $document.length === 0) {
                this.element.append('<div class="dcpDocument"></div>');
                this.$document = this.element.find(".dcpDocument");
            }
        },

        _initModel : function _initModel(initialValue) {
            var model = new DocumentModel(initialValue), currentWidget = this;
            this._model = model;
            this._initModelEvents();
            return model;
        },

        _initView : function _initView() {
            var documentView, $document;
            this._initDom();
            $document = this.$document;
            documentView = new DocumentView({model : this._model, el : $document[0]});
            this.view = documentView;
            this._initViewEvents();
            return documentView;
        },

        _reinitModel : function _reinitModel() {
            this._model.clear().set(this._getModelValue());
        },

        _initExternalElements : function _initExternalElements() {
            this.$loading = $(".dcpLoading").dcpLoading();
            this.$notification = $('body').dcpNotification(); // active notification
        },

        _initModelEvents : function _initEvents() {
            var currentWidget = this;
            this._model.on("invalid", function showInvalid(model, error) {
                currentWidget.$notification.dcpNotification("showError", error);
            });
            this._model.on("showError", function showError(error) {
                currentWidget.$notification.dcpNotification("showError", error);
            });
            this._model.on("sync", function() {
                currentWidget.options.initid = currentWidget._model.id;
                currentWidget.options.viewId = currentWidget._model.get("viewId");
                currentWidget.options.revision = currentWidget._model.get("revision");
            });
        },

        _initViewEvents : function _initViewEvents() {
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
                currentWidget.$loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
                _.delay(function () {
                    currentWidget.$loading.dcpLoading("hide");
                    console.timeEnd('main');
                }, 500);
            });
            this.view.on("showMessage", function showMessage(message) {
                currentWidget.$notification.dcpNotification("show", message.type, message);
            });
            this.view.on("showSuccess", function showSuccess(message) {
                currentWidget.$notification.dcpNotification("showSuccess", message);
            });
            this.view.on("reinit", function reinit() {
                currentWidget._initView();
                currentWidget.model.fetch();
            });
        },

        _initRouter : function _initRouter() {
            Backbone.history.start({pushState : true});
            this.router = new Router({document : this._model});
        },

        _destroy : function _destroy() {
            this.view.remove();
            delete this._model;
            this._trigger("destroy");
            this._super();
        },

        _getAttributeModel : function _getAttributeModel(attributeId) {
            var attribute = this._model.get("attributes").get(attributeId);
            if (!attribute) {
                throw new Error("The attribute " + attributeId + " doesn't exist");
            }
            return attribute;
        },

        getProperty : function getDocumentProperty(property) {
            return this._model.get("properties").get(property);
        },

        getProperties : function getDocumentProperties(property) {
            return this._model.get("properties").toJSON();
        },

        hideAttribute : function hideAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("hide");
        },

        showAttribute : function showAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("show");
        },

        reinitDocument : function () {
            this._reinitModel();
            this._model.fetch();
        },

        fetchDocument : function(options) {
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

        getValue : function(attributeId) {
            var attribute = this._model.get("attributes").get(attributeId);
            return attribute.get("value");
        },

        getValues : function() {
            var values = this._model.toJSON();
            return values.document.attributes;
        },

        setValue : function(attributeId, value) {
            var attribute = this._model.get("attributes").get(attributeId);
            return attribute.set("value", value);
        }

    });

    return $.fn.documentInternal;
});