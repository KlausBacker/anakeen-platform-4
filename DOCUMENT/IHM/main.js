/**
 * Main bootstraper
 */
/*global require*/
require([
    'jquery',
    'underscore',
    'backbone',
    'routers/router',
    'collections/documents',
    'models/document',
    'views/document/vDocument',
    'widgets/window/wConfirm',
    'widgets/window/wLoading'
], function ($, _, Backbone, Router, CollectionDocument, ModelDocument, ViewDocument) {
    'use strict';
    console.timeEnd("js loading");
    /*jshint nonew:false*/
    var $document, $loading, document, documentView;

    $document = $(".dcpDocument");

    $loading = $(".dcpLoading").dcpLoading();

    $('body').dcpNotification(); // active notification

    document = new ModelDocument({
        "initid" : window.dcp.viewData.documentIdentifier,
        "viewId" : window.dcp.viewData.vid
    });
    documentView = new ViewDocument({model : document, el : $document[0]});
    documentView.on('loading', function (data) {
        $loading.dcpLoading('setPercent', data);
    });
    documentView.on('loaderShow', function () {
        $loading.dcpLoading('show');
    });

    documentView.on('partRender', function () {
        $loading.dcpLoading('addItem');
    });
    documentView.on('renderDone', function () {
        console.timeEnd('documentRender');
        $loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
        _.delay(function () {
            $loading.dcpLoading("hide");
            console.timeEnd('main');
        }, 500);
    });
    console.time('documentRender');
    document.fetch({
        error :   function () {
            throw new Error("Unable to get the data from documents");
        }
    });
    window.dcp.document = document;
});
