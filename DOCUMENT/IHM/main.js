/*global require*/


// Require.js allows us to configure shortcut alias
require.config({
    // The shim config allows us to configure dependencies for
    // scripts that do not call define() to register a module
    shim: {
        underscore: {
            exports: '_'
        },
        backbone: {
            deps: [
                'underscore',
                'jquery'
            ],
            exports: 'Backbone'
        },
        bootstrap: {
            deps: [
                'jquery'
            ]
        },
        "kendo": {
            deps: [
                'jquery'
            ]
        },
        "kendo-culture": {
            deps: [
                'kendo'
            ]
        }
    },
    paths: {
        "jquery": "../../lib/KendoUI/js/jquery",
        "underscore": "../../lib/underscore/underscore",
        "backbone": "../../lib/backbone/backbone",
        "mustache": "../../lib/mustache.js/mustache",
        "bootstrap": "../../lib/bootstrap/js/bootstrap",
        "kendo": "../../lib/KendoUI/js/kendo.ui.core",
        "kendo-culture-fr": "../../lib/KendoUI/js/cultures/kendo.culture.fr-FR"
    }/*,
     urlArgs : "invalidateCache=" + (new Date()).getTime()*/
});

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
    'bootstrap'/*,
     'kendo'*/
], function ($, _, Backbone, Router, CollectionDocument, ModelDocument, ViewDocument) {
    'use strict';
    /*jshint nonew:false*/
    var document = window.dcp.documentData.document, model;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    $(".dcpLoading").dcpLoading();

    _.defer(function () {
        model = new ModelDocument(
            {},
            {properties: document.properties, menus: window.dcp.menu,
                family: window.dcp.documentData.family, attributes: document.attributes}
        );
        window.dcp.documents.push(model);
        (new ViewDocument({model: model, el: $(".dcpDocument")[0]}).render());


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
        router: new Router()
    };

    Backbone.history.start();
});
