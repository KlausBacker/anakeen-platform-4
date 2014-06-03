/*global require*/
'use strict';

// Require.js allows us to configure shortcut alias
require.config({
    // The shim config allows us to configure dependencies for
    // scripts that do not call define() to register a module
    shim :  {
        "widget" : {
            deps : [
                'jquery'
            ]
        },
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
        "jquery" :        "../../../lib/jquery/jquery",
        "widget" :        "./widgets/widget",
        "underscore" :    "../../../lib/underscore/underscore",
        "backbone" :      "../../../lib/backbone/backbone",
        "mustache" :      "../../../lib/mustache.js/mustache",
        "bootstrap" :     "../../../lib/bootstrap/js/bootstrap",
        "kendo" :         "../../../lib/KendoUI/js/kendo.ui.core"
    }/*,
     urlArgs : "invalidateCache=" + (new Date()).getTime()*/
});

require([
    'jquery',
    'underscore',
    'models/document',
    'views/document/document',
    'widget',
    'backbone',
    'bootstrap',
    'kendo'
], function ($, _, ModelDocument, ViewDocument) {
    /*jshint nonew:false*/
    var document = window.dcp.documentData.document;
    window.dcp = window.dcp || {};
    window.dcp.models = window.dcp.models || {};
    window.dcp.views = window.dcp.views || {};
    window.dcp.models.document = new ModelDocument(
        {},
        {properties : document.properties, menus : window.dcp.menu,
            family : window.dcp.documentData.family, attributes : document.attributes}
    );
    window.dcp.views.document = new ViewDocument({model : window.dcp.models.document, el : $(".dcpDocument")[0]}).render();
});