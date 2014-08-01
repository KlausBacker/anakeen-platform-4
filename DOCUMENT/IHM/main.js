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
    var document = window.dcp.documentData.document, model, $document;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    $document = $(".dcpDocument");

    $(".dcpLoading").dcpLoading();
    console.timeEnd('js loading');
    _.defer(function () {
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
        (new ViewDocument({model : model, el : $document[0]}).render());

        $document.show().addClass("dcpDocument--show");
        console.timeEnd('main');

        $(".dcpLoading").dcpLoading("complete", function () {
            $(".dcpDocument").show().addClass("dcpDocument--show");
        });

        _.delay(function () {
            //  $(".dcpLoading").dcpLoading("percent",100).fadeOut({duration:500});
            $(".dcpLoading").dcpLoading("percent", 100).addClass("dcpLoading--hide");
            _.delay(function () {
                $(".dcpLoading").dcpLoading("hide");
            }, 2000);
        }, 100);
    });
    window.dcp.router = {
        router : new Router()
    };

    Backbone.history.start();
});
