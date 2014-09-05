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
    var document = window.dcp.documentData.document, model, $document, $loading;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    $document = $(".dcpDocument");

    $loading = $(".dcpLoading").dcpLoading();
    console.timeEnd('js loading');
    _.defer(function () {
        var documentView;
        $('body').dcpNotification(); // active notification
        model = new ModelDocument(
            {},
            {
                properties : document.properties,
                menus :      window.dcp.menu,
                family :     window.dcp.documentData.family,
                locale :     window.dcp.user.locale,
                renderMode : window.dcp.renderOptions.mode || "read",
                attributes : document.attributes
            }
        );
        window.dcp.documents.push(model);
        $loading.dcpLoading('setNbItem', model.get("attributes").length);
        documentView = new ViewDocument({model : model, el : $document[0]});

        documentView.on('loading', function(data) {
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

    });
    window.dcp.router = {
        router : new Router()
    };

    Backbone.history.start();
});
