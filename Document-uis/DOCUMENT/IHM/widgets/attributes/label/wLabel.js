(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/widget'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function requireDcpLabel($, _, Mustache)
{
    'use strict';

    $.widget("dcp.dcpLabel", {
        options: {
            renderOptions: {
                helpLinkIdentifier: 0
            },
            labels: {
                helpTitle: "Info"
            }
        },
        _create: function wLabel_create()
        {
            this._initDom();
            this._initLinkHelpEvent();
        },

        _initDom: function wLabel_initDom()
        {
            this.element.addClass("dcpAttribute__label control-label dcpLabel");
            this.element.append(Mustache.render(this._getTemplate() || "", this.options));
            if (this.options.renderOptions && this.options.renderOptions.attributeLabel) {
                this.setLabel(this.options.renderOptions.attributeLabel);
            }
        },

        /**
         * Init event when a help is associated to the attribute
         *
         * @protected
         */
        _initLinkHelpEvent: function wLabelInitLinkEvent()
        {
            var helpId = this.options.renderOptions.helpLinkIdentifier;
            var scopeWidget = this;

            if (helpId) {
                this.element.on("click." + this.eventNamespace, '.dcpLabel__help__link', function wAttributeAttributeClick(event)
                {
                    var  eventContent;
                    var href = $(this).attr("href");
                    if (href.substring(0, 8) === "#action/") {
                        event.preventDefault();
                        console.log("event", event);
                        eventContent = href.substring(8).split(":");
                        scopeWidget._trigger("externalLinkSelected", event, {
                            target: event.target,
                            eventId: eventContent.shift(),
                            index: -1,
                            options: eventContent
                        });
                        return this;
                    }
                });
            }

            return this;
        },

        setLabel: function wLabelSetLabel(label)
        {
            this.element.find("label").text(label);
        },

        setError: function wLabelSetError(message)
        {
            if (message) {
                this.element.addClass("has-error");
            } else {
                this.element.removeClass("has-error");
            }
        },

        _getTemplate: function wLabel_getTemplate()
        {
            if (this.options.templates && this.options.templates.label) {
                return this.options.templates.label;
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates.label) {
                return window.dcp.templates.label;
            }
            throw new Error("Unknown label template ");
        }
    });

    return $.fn.dcpLabel;
}));