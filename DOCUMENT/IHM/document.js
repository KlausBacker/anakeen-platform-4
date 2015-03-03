define([
    'underscore',
    'jquery',
    'dcpDocument/widgets/widget'
], function (_, $)
{
    'use strict';

    $.widget("dcp.document", {

        _template: _.template('<iframe class="dcpDocumentWrapper" src="?app=DOCUMENT&id=<%= id %>"></iframe>'),

        _create: function dcpDocument_create()
        {
            if (!this.options.id) {
                throw new Error("Unable to create a document without index");
            }
            this._render();
        },

        _render: function dcpDocument_render()
        {
            var innerWindow, $iframe, currentWidgetObject = this.element.data(this.widgetFullName);
            //inject the iframe
            this.element.append(this._template(this.options));
            //bind the internal controller to the documentWidget
            $iframe = this.element.find("iframe");
            //Listen the load to the iframe (initial JS added and page loaded)
            $iframe.on("load", function dcpDocument_setReadyEvent()
            {
                var loadedCallback = function dcpDocument_loadedCallback(domNode) {
                    //Bind the internalController function to the current widget
                    var widgetController = domNode.data("dcpDocumentController"), key;
                    for(key in widgetController) {
                        //noinspection JSUnfilteredForInLoop
                        if ((currentWidgetObject[key] === void 0) && _.isFunction(widgetController[key]) && key.charAt(0) !== "_") {
                            //noinspection JSUnfilteredForInLoop
                            currentWidgetObject[key] = _.bind(widgetController[key], widgetController);
                        }
                    }
                };
                //Find the iframe object
                innerWindow = $iframe[0];
                //Inhect in the iframe window a callback function used by the internalController
                if (_.isFunction(innerWindow.contentWindow.documentLoaded)) {
                    //Wrap is there is a function with the same name
                    loadedCallback = _.wrap(innerWindow.contentWindow.documentLoaded, function dcpDocument_wrapLoaded(loaded) {
                        try {
                            loaded.apply(this, _.rest(arguments));
                        } catch(e) {
                            console.error(e);
                        }
                        loadedCallback.apply(this, _.rest(arguments));
                    });
                }
                innerWindow.contentWindow.documentLoaded = loadedCallback;
            });
        }
    });

});
