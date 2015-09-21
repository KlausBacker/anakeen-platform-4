define([
    'underscore',
    'mustache',
    'jquery',
    'dcpDocument/widgets/attributes/wAttribute',
    'kendo/kendo.autocomplete'
], function wText(_, Mustache, $)
{
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options: {
            type: "text",
            renderOptions: {
                maxLength: 0, // char max length
                placeHolder: '',
                format: ""
            }
        },

        kendoWidget: null,

        _initDom: function wTextInitDom()
        {
            if (this.getMode() === "read") {
                if (this.options.renderOptions.format) {
                    this.options.attributeValue.formatValue = Mustache.render(this.options.renderOptions.format,
                        this.options.attributeValue);
                }
            }

            this._super();
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            if (this.kendoWidget && this.options.hasAutocomplete) {
                this.activateAutocomplete(this.kendoWidget);
            } else {
                if (this.getType() === "text") {
                    this.kendoWidget.addClass("k-textbox");
                }
            }
        },

        _initEvent: function wTextInitEvent()
        {
            if (this.getMode() === "write") {
                this._initChangeEvent();
            }
            this._super();
        },

        _initChangeEvent: function wTextInitChangeEvent()
        {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.getContentElements().on("change" + this.eventNamespace, function wTextChangeElement()
                {
                    var newValue = _.clone(currentWidget.options.attributeValue);
                    newValue.value = $(this).val();
                    newValue.displayValue = newValue.value;
                    currentWidget.setValue(newValue);
                });
            }
        },

        /**
         * Just to be apply in normal input help
         * @param inputValue
         */
        activateAutocomplete: function activateAutocomplete(inputValue)
        {
            var currentWidget = this;
            inputValue.kendoAutoComplete({
                dataTextField: "title",
                filter: "contains",
                minLength: 1,
                template: '<span><span class="k-state-default">#= data.title#</span>' +
                '#if (data.error) {#' +
                '<span class="k-state-error">#: data.error#</span>' +
                '#}# </span>',
                dataSource: {
                    type: "json",

                    serverFiltering: true,
                    transport: {
                        read: function mapAutoActivated(options) {
                            options.data.index=currentWidget._getIndex();
                            return currentWidget.options.autocompleteRequest.call(null, options);
                        }
                    }
                },
                filtering: function wTextFiltering(e) {
                    // space search is used to force new search
                    if (e.filter.value === " ") {
                        e.filter.value='';
                    }
                },
                select: function kendoAutocompleteSelect(event)
                {
                    var valueIndex = currentWidget._getIndex();
                    var dataItem = this.dataSource.at(event.item.index());
                    //The object returned by dataSource.at are internal kendo object so I clean it with toJSON
                    if (dataItem.toJSON) {
                        dataItem = dataItem.toJSON();
                    }
                    event.preventDefault(); // no fire change event
                    currentWidget._trigger("changeattrsvalue", event, {dataItem: dataItem, valueIndex: valueIndex});
                }
            });
            this.element.on("click" + this.eventNamespace, '.dcpAttribute__value--autocomplete--button', function wTextClickAutoComplete(event)
            {
                event.preventDefault();
                inputValue.data("kendoAutoComplete").search(' '); // use space search
            });
            this.element.find('.dcpAttribute__value--autocomplete--button[title]').tooltip({
                html: true
            });

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue: function wTextSetValue(value)
        {

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
            } else
                if (this.getMode() === "read") {
                    this.redraw();
                } else {
                    throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
                }
        },

        getType: function getType()
        {
            return "text";
        },

        _destroy: function _destroy()
        {
            //Destroy autocomplete if activated
            if (this.kendoWidget && this.kendoWidget.data("kendoAutoComplete")) {
                this.kendoWidget.data("kendoAutoComplete").destroy();
            }
            this._super();
        }

    });

    return $.fn.dcpText;
});