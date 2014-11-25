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
    var model, $document, $loading, urlData;
    window.dcp = window.dcp || {};
    window.dcp.documents = new CollectionDocument();
    window.dcp.views = window.dcp.views || {};

    $document = $(".dcpDocument");

    $loading = $(".dcpLoading").dcpLoading();
    console.timeEnd('js loading');

    $('body').dcpNotification(); // active notification

    urlData = "api/v1/documents/" + window.dcp.viewData.documentIdentifier;
    if (window.dcp.viewData.revision >= 0) {
        urlData += "/revisions/" + window.dcp.viewData.revision;
    }
    urlData += "/views/" + window.dcp.viewData.vid;
    $.getJSON(urlData)
        .done(function (data) {
            console.log("view", data);
            var documentView;
            var properties = data.data.view.documentData.document.properties;
            var customCss = data.data.view.style.css;
            var customJs = data.data.view.script.js;

            //@TODO not use global variables
            window.dcp.renderOptions = data.data.view.renderOptions;
            window.dcp.templates = data.data.view.templates;

            // change window title and icon
            window.document.title = properties.title;
            $("link[rel='shortcut icon']").attr("href", properties.icon);
            $('.dcpLoading--title').text(properties.title);
            $('.dcpLoading--header img').attr("src", properties.icon);
            $('.dcpDocument').addClass("dcpDocument--" + properties.status);

            // add custom css style
            _.each(customCss, function (cssItem) {
                var $existsLink = $('link[rel=stylesheet][data-id=' + cssItem.key + ']');
                if ($existsLink.length === 0) {
                    $("head link[rel='stylesheet']").last().
                        after('<link rel="stylesheet" type="text/css" href="' + cssItem.path + '" data-id="' + cssItem.key + '" >');
                }
            });


            model = new ModelDocument(
                {},
                {
                    properties: data.data.view.documentData.document.properties,
                    menus: data.data.view.menu,
                    family: data.data.view.documentData.family || {structure: {}},
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

                // add custom js script
                _.each(customJs, function (jsItem) {
                    var $existsLink = $('script[data-id=' + jsItem.key + ']');
                    console.log("add js", jsItem, $existsLink);
                    if ($existsLink.length === 0) {
                        $("head link[rel='stylesheet']").last().
                            after('<script type="text/javascript" src="' + jsItem.path + '" data-id="' + jsItem.key + '" >');
                    }
                });
                $(".dcpDocument").show();
            });

        })
        .fail(function (response) {
            var result = JSON.parse(response.responseText);
            console.log("error", result, result.exceptionMessage);

            _.each(result.messages, function (error) {
                if (error.type === "error") {
                    $('body').trigger("notification", {
                        type: error.type,
                        message: error.contentText
                    });
                }
            });
        });
    window.dcp.router = {
        router: new Router()
    };

    Backbone.history.start();
});
