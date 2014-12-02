/**
 * Main bootstraper
 */
/*global require*/
require([
    'jquery',
    'underscore',
    'backbone',
    'routers/router',
    'models/document',
    'views/document/vDocument',
    'widgets/window/wConfirm',
    'widgets/window/wLoading',
    'bootstrap'
], function ($, _, Backbone, Router, ModelDocument, ViewDocument) {
    'use strict';
    console.timeEnd("js loading");
    /*jshint nonew:false*/
    var $loading, document, documentView, $notification;

    $loading = $(".dcpLoading").dcpLoading();

    $notification = $('body').dcpNotification(); // active notification

    // TODO : Move the code of main in the document widget controller
    function initModel(initialValue) {
        var document = new ModelDocument(initialValue);
        document.on("invalid", function showInvalid(model, error) {
            $notification.dcpNotification("showError", error);
        });
        document.on("showError", function showError(error) {
            $notification.dcpNotification("showError", error);
        });
        return document;
    }
    function initView(document) {
        var documentView, $document = $(".dcpDocument");
        if ($document.length === 0) {
            $("body").prepend('<div class="dcpDocument"></div>');
            $document = $(".dcpDocument");
        }
        documentView = new ViewDocument({model : document, el : $document[0]});
        documentView.on("cleanNotification", function () {
            $notification.dcpNotification("clear");
        });
        documentView.on('loading', function (data) {
            $loading.dcpLoading('setPercent', data);
        });
        documentView.on('loaderShow', function () {
            console.time("hotRender");
            $loading.dcpLoading('show');
        });
        documentView.on('loaderHide', function () {
            $loading.dcpLoading('hide');
        });
        documentView.on('partRender', function () {
            $loading.dcpLoading('addItem');
        });
        documentView.on('renderDone', function () {
            console.timeEnd("hotRender");
            $loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
            _.delay(function () {
                $loading.dcpLoading("hide");
                console.timeEnd('main');
            }, 500);
        });
        documentView.on("showSuccess", function showError(message) {
            $notification.dcpNotification("showSuccess", message);
        });
        return documentView;
    }

    document = initModel({
        "initid" : window.dcp.viewData.documentIdentifier,
        "viewId" : window.dcp.viewData.vid
    });
    documentView = initView(document);
    document.fetch();

    documentView.on("reinit", function reinit() {
        documentView = initView(document);
        document.fetch();
    });

    window.dcp.document = document;
    window.dcp.documentView = documentView;
});
