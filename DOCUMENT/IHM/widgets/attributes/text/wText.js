define([
    'underscore',
    'mustache',
    "kendo-culture-fr",
    '../wAttribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options: {
            id: "",
            type: "text"
        },
        kendoWidget: null,
        _initDom: function () {

            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget && this.options.hasAutocomplete) {
                this.activateAutocomplete(this.kendoWidget);
            }

        },

        _initEvent: function _initEvent() {
            if (this.getMode() === "write") {
                this._initChangeEvent();
            }
            if (this.getMode() === "read") {
                this._initLinkEvent();
            }
            this._super();
        },





        _initChangeEvent: function _initChangeEvent() {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.contentElements().on("change." + this.eventNamespace, function () {
                    var newValue = _.clone(currentWidget.options.value);
                    newValue.value = $(this).val();
                    newValue.displayValue=newValue.value;
                    currentWidget.setValue(newValue);
                });
            }
        },

        /**
         * Just to be apply in normal input help
         * @param inputValue
         * @protected
         */
        activateAutocomplete: function (inputValue) {
            var scope = this;
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

                        read : scope.options.autocompleteRequest

                    }
                },
                select: function (event) {
                    var valueIndex = scope._getIndex();
                    var dataItem = this.dataItem(event.item.index());
                    event.preventDefault(); // no fire change event
                    scope._trigger("changeattrsvalue", event, [dataItem, valueIndex]  );

                }
            });
            this.element.find('.dcpAttribute__content--autocomplete--button').on("click", function (event) {
                event.preventDefault();
                inputValue.data("kendoAutoComplete").search(' ');
            });
            this.element.find('.dcpAttribute__content--autocomplete--button[title]').kendoTooltip();

        },

        /**
         * Modify value to widget and send notification to the view
         * @param value
         */
        setValue: function wTextSetValue(value) {

            // call wAttribute:::setValue() :send notification
            this._super(value);
            // var contentElement = this.element.find('.dcpAttribute__content[name="'+this.options.id+'"]');
            var contentElement = this.contentElements();
            var originalValue = this.getWidgetValue();

            if (this.getMode() === "write") {
                // : explicit lazy equal
                //noinspection JSHint
                console.log("Try text here", {newv:value.value,ori:originalValue} );
               if (value.value === null && originalValue === '') {
                    originalValue=null;
                }
                if (originalValue !== value.value) {
                    // Modify value only if different
                    console.log("Modify text here", {newv:value.value,ori:this.getWidgetValue()} );
                    this.contentElements().val(value.value);
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                console.log("READ UPDATE TO", this.options.id,value);
                contentElement.text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },



        getType: function () {
            return "text";
        }

    });
});