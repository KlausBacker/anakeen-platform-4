/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/attributes/label/wLabel',
    'widgets/attributes/text/wText',
    'widgets/attributes/int/wInt',
    'widgets/attributes/longtext/wLongtext',
    'widgets/attributes/htmltext/wHtmltext',
    'widgets/attributes/timestamp/wTimestamp',
    'widgets/attributes/double/wDouble',
    'widgets/attributes/docid/wDocid'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className: "row dcpAttribute form-group",

        events: {
            "dcpattributechange .dcpAttribute__contentWrapper": "updateValue"
        },

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'change:errorMessage', this.refreshError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = window.dcp.templates.attribute.simpleWrapper;
        },

        render: function () {
            console.time("render attribute " + this.model.id);
            var data = this.model.toJSON();
            data.viewCid = this.cid;
            data.renderOptions = this.model.getOptions();
            this.$el.addClass("dcpAttribute--type--" + this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            if (this.model.get("needed")) {
                this.$el.addClass("dcpAttribute--needed");
            }
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__label").dcpLabel(data);


            this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), data);

            console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        refreshLabel: function () {
            this.getDOMElements().find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        refreshValue: function () {
            console.log("propagate setvalue to view", this.model.id);
            var allWrapper = this.getDOMElements().find(".dcpAttribute__contentWrapper").add(this.getDOMElements().filter(".dcpAttribute__contentWrapper"));
            var values = this.model.get("value");
            var scope = this;
            if (this.model.inArray()) {
                values = _.toArray(values);
                allWrapper.each(function (index, element) {
                    scope.widgetApply($(element), "setValue", values[index]);
                });

            } else {
                this.widgetApply(allWrapper, "setValue", values);
            }

        },
        refreshError: function () {
            this.$el.find(".dcpAttribute__label").dcpLabel("setError", this.model.get("errorMessage"));
            this.widgetApply(this.getDOMElements().find(".dcpAttribute__contentWrapper"), "setError", this.model.get("errorMessage"));
        },


        getDOMElements: function () {
            if (this.options.els) {
                return this.options.els();
            } else {
                return this.$el;
            }
        },
        updateValue: function () {
            console.log("view has receive change", this.model.id);
            this.model.setValue(this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), "getValue"));
        },

        widgetApply: function ($element, method, argument) {
            return this.getWidgetClass().apply($element, [method, argument]);
        },

        getWidgetClass: function () {
            return this.getTypedWidgetClass(this.model.get("type"));
        },


        getTypedWidgetClass: function (type) {
            switch (type) {
                case "text" :
                    return $.fn.dcpText;
                case "int" :
                    return $.fn.dcpInt;
                case "double" :
                    return $.fn.dcpDouble;
                case "longtext" :
                    return $.fn.dcpLongtext;
                case "htmltext" :
                    return $.fn.dcpHtmltext;
                case "date" :
                    return $.fn.dcpDate;
                case "timestamp" :
                    return $.fn.dcpTimestamp;
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        }
    });


});