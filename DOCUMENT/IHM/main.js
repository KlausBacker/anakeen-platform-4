/*global require*/
'use strict';

// Require.js allows us to configure shortcut alias
require.config({
    // The shim config allows us to configure dependencies for
    // scripts that do not call define() to register a module
    shim :  {
        underscore :      {
            exports : '_'
        },
        backbone :        {
            deps :    [
                'underscore',
                'jquery'
            ],
            exports : 'Backbone'
        },
        bootstrap :       {
            deps : [
                'jquery'
            ]
        },
        "kendo" :         {
            deps : [
                'jquery'
            ]
        },
        "kendo-culture" : {
            deps : [
                'jquery'
            ]
        }
    },
    paths : {
        "jquery" :        "../../lib/jquery/jquery",
        "underscore" :    "../../lib/underscore/underscore",
        "backbone" :      "../../lib/backbone/backbone",
        "mustache" :      "../../lib/mustache.js/mustache",
        "bootstrap" :     "../../lib/bootstrap/js/bootstrap",
        "kendo" :         "../../lib/KendoUI/js/kendo.ui.core"
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
    'views/document/document',
    'bootstrap'/*,
    'kendo'*/
], function ($, _, Backbone, Router, CollectionDocument, ModelDocument, ViewDocument) {
    /*jshint nonew:false*/
    var document = window.dcp.documentData.document, model;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    model = new ModelDocument(
        {},
        {properties : document.properties, menus : window.dcp.menu,
            family :  window.dcp.documentData.family, attributes : document.attributes}
    );
    window.dcp.documents.push(model);
    (new ViewDocument({model : model, el : $(".dcpDocument")[0]}).render());
    $(".dcpLoading").hide();
    $(".dcpDocument").show();
    window.dcp.router = {
        router : new Router()
    };

    Backbone.history.start();
});
