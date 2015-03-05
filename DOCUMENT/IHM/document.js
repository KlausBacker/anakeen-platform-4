/*global define*/
define([
    'underscore',
    'jquery',
    'dcpDocument/widgets/widget'
], function (_, $)
{
    'use strict';

    $.widget("dcp.document", {

        _template: _.template('<iframe class="dcpDocumentWrapper"  style="border : 0;" src="?app=DOCUMENT&id=<%= options.initid %><% if (options.viewId) { %> &vid=<%= options.viewId %> <% } %><% if (options.revision) { %> &revision=<%= options.revision %> <% } %>"></iframe>'),

        _create: function dcpDocument_create()
        {
            if (!this.options.initid) {
                throw new Error("Unable to create a document without initid");
            }
            this._render();
            this._bindEvents();
        },

        _render: function dcpDocument_render()
        {
            var innerWindow, $iframe, currentWidgetObject = this.element.data(this.widgetFullName), currentWidget = this;
            //inject the iframe
            this.element.append(this._template({options : this.options}));
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
                        if (/*(currentWidgetObject[key] === void 0) &&*/ _.isFunction(widgetController[key]) && key.charAt(0) !== "_") {
                            //noinspection JSUnfilteredForInLoop
                            currentWidgetObject[key] = _.bind(widgetController[key], widgetController);
                        }
                    }
                    currentWidget._trigger("ready");
                };
                //Find the iframe object
                innerWindow = $iframe[0];
                //Inject in the iframe window a callback function used by the internalController
                innerWindow.contentWindow.documentLoaded = loadedCallback;
            }).trigger("load");
        },

        _bindEvents : function dcpDocument_bindEvents() {
            $(window).resize(_.debounce(_.bind(this._resize, this), 50));
            this._resize();
        },

        _resize : function dcpDocument_resize() {
            var event = this._trigger("resize"),
                $documentWrapper = this.element.find(".dcpDocumentWrapper"),
                element = this.element;
            //the computation can be done by an external function and default prevented
            if (event) {
                //compute two times height (one for disapear horizontal scrollbar, two to get the actual size)
                $documentWrapper.height(element.innerHeight() - 3);
                $documentWrapper.width(element.innerWidth());
                //defer height computation to let the time to scrollbar disapear
                _.defer(function dcpDocument_computeHeight() {
                    $documentWrapper.height(element.innerHeight() - 3);
                });
            }
        }
    });

});
