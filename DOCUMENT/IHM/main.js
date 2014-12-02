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
    var $document, $loading, document, documentView, $notification;

    $document = $(".dcpDocument");

    $loading = $(".dcpLoading").dcpLoading();

    $notification = $('body').dcpNotification(); // active notification

    // TODO : Move the code of main in the document widget controller

    document = new ModelDocument({
        "initid" : window.dcp.viewData.documentIdentifier,
        "viewId" : window.dcp.viewData.vid
    });
    documentView = new ViewDocument({model : document, el : $document[0]});

    documentView.on("cleanNotification", function() {
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

    document.fetch({
        error :   function () {
            throw new Error("Unable to get the data from documents");
        }
    });

    document.on("invalid", function showInvalid(model, error) {
        $notification.dcpNotification("showError", error);
    });

    document.on("showError", function showError(error) {
        $notification.dcpNotification("showError", error);
    });

    window.dcp.document = document;
});
