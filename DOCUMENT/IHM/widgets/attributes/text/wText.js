define([
    'underscore',
    'widgets/attributes/wAttribute',
    'kendo/kendo.autocomplete'
], function (_) {
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options : {
            type : "text"
        },

        kendoWidget : null,

        _initDom : function wTextInitDom() {
            this._super();
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");

            if (this.kendoWidget && this.options.hasAutocomplete) {
                this.activateAutocomplete(this.kendoWidget);
            } else {
                if (this.getType() === "text") {
                    this.kendoWidget.addClass("k-textbox");
                }
            }
        },

        _initEvent : function wTextInitEvent() {
            if (this.getMode() === "write") {
                this._initChangeEvent();
            }
            this._super();
        },

        _initChangeEvent : function wTextInitChangeEvent() {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.getContentElements().on("change." + this.eventNamespace, function () {
                    var newValue = _.clone(currentWidget.options.value);
                    newValue.value = $(this).val();
                    newValue.displayValue = newValue.value;
                    currentWidget.setValue(newValue);
                });
            }
        },

        /**
         * Just to be apply in normal input help
         * @param inputValue
         * @protected
         */
        activateAutocomplete : function (inputValue) {
            var scope = this;
            inputValue.kendoAutoComplete({
                dataTextField : "title",
                filter :        "contains",
                minLength :     1,
                template :      '<span><span class="k-state-default">#= data.title#</span>' +
                                    '#if (data.error) {#' +
                                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>',

                dataSource : {
                    type :            "json",
                    serverFiltering : true,
                    transport :       {

                        read : scope.options.autocompleteRequest

                    }
                },
                select :     function (event) {
                    var valueIndex = scope._getIndex();
                    var dataItem = this.dataItem(event.item.index());
                    event.preventDefault(); // no fire change event
                    scope._trigger("changeattrsvalue", event, [dataItem, valueIndex]);

                }
            });
            this.element.find('.dcpAttribute__content--autocomplete--button').on("click", function (event) {
                event.preventDefault();
                inputValue.data("kendoAutoComplete").search(' ');
            });
            this.element.find('.dcpAttribute__content--autocomplete--button[title]').tooltip();

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue : function wTextSetValue(value) {

            var originalValue;

            value = _.clone(value);

            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = value.value;
            }
            this._super(value);

            originalValue = this.getWidgetValue();

            if (this.getMode() === "write") {
                // : explicit lazy equal
                if (value.value === null && originalValue === '') {
                    originalValue = null;
                }
                if (originalValue !== value.value) {
                    // Modify value only if different
                    this.getContentElements().val(value.value);
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.redraw();
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        getType : function () {
            return "text";
        }

    });

    return $.fn.dcpText;
});