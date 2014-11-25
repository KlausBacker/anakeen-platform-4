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
    'widgets/window/wLoading',
    'bootstrap'
], function ($, _, Backbone, Router, CollectionDocument, ModelDocument, ViewDocument) {
    'use strict';
    console.timeEnd("js loading");
    /*jshint nonew:false*/
    var  model, $document, $loading;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    $document = $(".dcpDocument");

    $loading = $(".dcpLoading").dcpLoading();
    console.timeEnd('js loading');

    $.getJSON("api/v1/documents/" + window.dcp.viewData.documentIdentifier + "/views/"+window.dcp.viewData.vid)
        .done(function (data) {
            console.log("view", data);
            var documentView;
            $('body').dcpNotification(); // active notification

            //@TODO not use global variables
            window.dcp.renderOptions = data.data.view.renderOptions;
            window.dcp.templates = data.data.view.templates;

            model = new ModelDocument(
                {},
                {
                    properties: data.data.view.documentData.document.properties,
                    menus: data.data.view.menu,
                    family: data.data.view.documentData.family || {structure:{}},
                    locale: data.data.view.locale.culture,
                    renderMode: data.data.view.renderOptions.mode || "read",
                    attributes: data.data.view.documentData.document.attributes
                }
            );
            window.dcp.documents.push(model);
            $loading.dcpLoading('setNbItem', model.get("attributes").length);
            documentView = new ViewDocument({model: model, el: $document[0]});

            documentView.on('loading', function (data) {
                $loading.dcpLoading('setPercent', data);
            });

            documentView.on('partRender', function () {
                $loading.dcpLoading('addItem');
            });

            documentView.on('renderDone', function () {
                $loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
                _.delay(function () {
                    $loading.dcpLoading("hide");
                }, 500);
            });

            documentView.render();

            console.timeEnd('main');

            $loading.dcpLoading("complete", function () {

                $(".dcpDocument").show();
            });

        })
        .fail(function (response) {
            var result = JSON.parse(response.responseText);
            console.log("error", result);
            if (result.exceptionMessage) {

            }
        });
    window.dcp.router = {
        router: new Router()
    };

    Backbone.history.start();
});
